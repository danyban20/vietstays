<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\HostCustomerMessage;
use App\Models\HostCustomerMessageThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The guest's side of host ↔ customer messaging. Threads belong to a host
 * and a customer key (see CustomerAggregationService); a signed-in guest
 * sees every thread keyed to one of their own bookings, plus an empty
 * conversation for each host they have booked with but not written to.
 */
class GuestMessagingService
{
    public function __construct(
        protected GuestStayService $stays,
        protected CustomerAggregationService $customers,
    ) {}

    /** @var array<int, string|null> thread id => first visible message time */
    private array $visibleFrom = [];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function conversations(User $member): array
    {
        $bookings = $this->bookings($member);
        $threads = $this->threadsFor($member, $bookings);
        $rows = [];
        $hostsWithThread = [];

        foreach ($threads as $thread) {
            $hostsWithThread[$thread->user_id] = true;
            $rows[] = $this->presentConversation($thread, $thread->user, $this->bookingsForHost($bookings, $thread->user));
        }

        foreach ($this->hostsOf($bookings) as $hostId => $host) {
            if (isset($hostsWithThread[$hostId])) {
                continue;
            }

            $rows[] = $this->presentConversation(null, $host, $this->bookingsForHost($bookings, $host));
        }

        usort($rows, fn (array $a, array $b) => strcmp((string) $b['sort_key'], (string) $a['sort_key']));

        return array_map(function (array $row) {
            unset($row['sort_key']);

            return $row;
        }, $rows);
    }

    /**
     * The conversation to open for "Message host" on a booking.
     */
    public function conversationIdForBooking(User $member, Booking $booking): ?string
    {
        $host = $this->stays->hostFor($booking->apartment);

        if (! $host) {
            return null;
        }

        $thread = $this->threadsFor($member, $this->bookings($member))
            ->first(fn (HostCustomerMessageThread $thread) => $thread->user_id === $host->id);

        return $thread ? (string) $thread->id : 'host-'.$host->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function show(User $member, string $conversationId): array
    {
        [$thread, $host] = $this->resolve($member, $conversationId);
        $bookings = $this->bookings($member);
        $messages = [];

        if ($thread) {
            $messages = $this->messagesQuery($thread)
                ->orderBy('created_at')
                ->get()
                ->map(fn (HostCustomerMessage $message) => [
                    'id' => $message->id,
                    'mine' => $message->sender === 'guest',
                    'author' => $message->sender === 'guest' ? 'You' : ($message->author ?: $this->hostName($host)),
                    'body' => $message->body,
                    'booking_id' => $message->booking_id,
                    'sent_at' => $message->created_at->toIso8601String(),
                ])
                ->values()
                ->all();

            $thread->update(['guest_last_read_at' => now()]);
        }

        return $this->presentConversation($thread?->fresh(), $host, $this->bookingsForHost($bookings, $host)) + [
            'messages' => $messages,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function send(User $member, string $conversationId, string $body, ?int $bookingId = null): array
    {
        [$thread, $host] = $this->resolve($member, $conversationId);
        $bookings = $this->bookingsForHost($this->bookings($member), $host);

        if ($bookingId !== null && ! $bookings->contains(fn (Booking $booking) => $booking->ID === $bookingId)) {
            $bookingId = null;
        }

        if (! $thread) {
            $latest = $bookings->sortByDesc(fn (Booking $booking) => $booking->check_in_date)->first();

            if (! $latest) {
                abort(404);
            }

            $key = $this->customers->customerKeyForBooking($latest);
            $key = $this->customers->resolveMergeMap($host)[$key] ?? $key;

            $thread = HostCustomerMessageThread::query()->firstOrCreate(
                ['user_id' => $host->id, 'customer_key' => $key],
                ['guest_token' => Str::random(48)],
            );
        }

        HostCustomerMessage::query()->create([
            'thread_id' => $thread->id,
            'booking_id' => $bookingId,
            'sender' => 'guest',
            'author' => $member->display_name ?: $member->name,
            'body' => $body,
        ]);

        $thread->update(['last_message_at' => now(), 'guest_last_read_at' => now()]);

        return $this->show($member, (string) $thread->id);
    }

    /**
     * Unread host messages across all of the guest's conversations.
     *
     * @return array{count: int, from: string|null, conversation_id: string|null}
     */
    public function unread(User $member): array
    {
        $count = 0;
        $from = null;
        $conversationId = null;

        foreach ($this->threadsFor($member, $this->bookings($member)) as $thread) {
            $unread = $this->unreadCount($thread);

            if ($unread > 0) {
                $count += $unread;
                $from ??= $this->hostName($thread->user);
                $conversationId ??= (string) $thread->id;
            }
        }

        return ['count' => $count, 'from' => $from, 'conversation_id' => $conversationId];
    }

    /**
     * @return array{0: HostCustomerMessageThread|null, 1: User}
     */
    protected function resolve(User $member, string $conversationId): array
    {
        $bookings = $this->bookings($member);

        if (str_starts_with($conversationId, 'host-')) {
            $hostId = (int) Str::after($conversationId, 'host-');
            $host = $this->hostsOf($bookings)->get($hostId);

            if (! $host) {
                abort(404);
            }

            $thread = $this->threadsFor($member, $bookings)
                ->first(fn (HostCustomerMessageThread $thread) => $thread->user_id === $host->id);

            return [$thread, $host];
        }

        $thread = $this->threadsFor($member, $bookings)
            ->first(fn (HostCustomerMessageThread $thread) => (string) $thread->id === $conversationId);

        if (! $thread || ! $thread->user) {
            abort(404);
        }

        return [$thread, $thread->user];
    }

    /**
     * @return Collection<int, Booking>
     */
    protected function bookings(User $member): Collection
    {
        return $this->stays->bookingsQuery($member)->orderByDesc('check_in_date')->get();
    }

    /**
     * Hosts (apartment owners) of the guest's bookings, keyed by user id.
     *
     * @param  Collection<int, Booking>  $bookings
     * @return Collection<int, User>
     */
    protected function hostsOf(Collection $bookings): Collection
    {
        return $bookings
            ->map(fn (Booking $booking) => $this->stays->hostFor($booking->apartment))
            ->filter()
            ->keyBy('id');
    }

    /**
     * Threads between the guest and the hosts they booked with from this
     * account. The customer key comes from the email typed at checkout,
     * which is not verified, so a thread only counts when its host owns one
     * of the guest's booked apartments, and only messages from that first
     * booking onwards are shown (see since()).
     *
     * @param  Collection<int, Booking>  $bookings
     * @return Collection<int, HostCustomerMessageThread>
     */
    protected function threadsFor(User $member, Collection $bookings): Collection
    {
        $threads = collect();

        foreach ($this->hostsOf($bookings) as $host) {
            $mergeMap = $this->customers->resolveMergeMap($host);
            $keys = $this->bookingsForHost($bookings, $host)
                ->map(fn (Booking $booking) => $this->customers->customerKeyForBooking($booking))
                ->map(fn (string $key) => $mergeMap[$key] ?? $key)
                ->unique()
                ->values();

            $thread = HostCustomerMessageThread::query()
                ->where('user_id', $host->id)
                ->whereIn('customer_key', $keys)
                ->orderByDesc('last_message_at')
                ->first();

            if ($thread) {
                $thread->setRelation('user', $host);
                $this->visibleFrom[$thread->id] = $this->since($bookings, $host);
                $threads->push($thread);
            }
        }

        return $threads;
    }

    /**
     * Messages in a thread older than the guest's first booking with that
     * host belong to whoever used the email before, not to this account.
     *
     * @param  Collection<int, Booking>  $bookings
     */
    protected function since(Collection $bookings, User $host): ?string
    {
        $first = $this->bookingsForHost($bookings, $host)
            ->map(fn (Booking $booking) => $booking->dateadded)
            ->filter()
            ->min();

        return $first?->format('Y-m-d H:i:s');
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @return Collection<int, Booking>
     */
    protected function bookingsForHost(Collection $bookings, User $host): Collection
    {
        return $bookings
            ->filter(fn (Booking $booking) => (int) ($booking->apartment?->user_id ?? 0) === (int) $host->legacy_wp_id)
            ->values();
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @return array<string, mixed>
     */
    protected function presentConversation(?HostCustomerMessageThread $thread, User $host, Collection $bookings): array
    {
        $last = $thread
            ? $this->messagesQuery($thread)->orderByDesc('created_at')->first()
            : null;
        $latestBooking = $bookings->first();

        return [
            'id' => $thread ? (string) $thread->id : 'host-'.$host->id,
            'host_name' => $this->hostName($host),
            'host_initials' => $this->initials($this->hostName($host)),
            'apartments' => $bookings
                ->map(fn (Booking $booking) => $this->stays->apartmentName($booking->apartment))
                ->unique()
                ->values()
                ->all(),
            'bookings' => $bookings
                ->map(fn (Booking $booking) => [
                    'id' => $booking->ID,
                    'label' => $this->stays->apartmentName($booking->apartment)
                        .' · '.$booking->check_in_date->format('M j').' – '.$booking->check_out_date->format('M j, Y'),
                ])
                ->values()
                ->all(),
            'default_booking_id' => $latestBooking?->ID,
            'last_message' => $last ? Str::limit($last->body, 120) : null,
            'last_message_mine' => $last?->sender === 'guest',
            'last_message_at' => $thread?->last_message_at?->toIso8601String(),
            'unread' => $thread ? $this->unreadCount($thread) : 0,
            'sort_key' => $thread?->last_message_at?->format('Y-m-d H:i:s.u')
                ?? ($latestBooking?->check_in_date?->format('Y-m-d H:i:s') ?? ''),
        ];
    }

    protected function messagesQuery(HostCustomerMessageThread $thread): Builder
    {
        return HostCustomerMessage::query()
            ->where('thread_id', $thread->id)
            ->when(
                $this->visibleFrom[$thread->id] ?? null,
                fn (Builder $query, string $from) => $query->where('created_at', '>=', $from),
            );
    }

    protected function unreadCount(HostCustomerMessageThread $thread): int
    {
        return $this->messagesQuery($thread)
            ->where('sender', '!=', 'guest')
            ->when(
                $thread->guest_last_read_at,
                fn (Builder $query) => $query->where('created_at', '>', $thread->guest_last_read_at->format('Y-m-d H:i:s.u')),
            )
            ->count();
    }

    protected function hostName(?User $host): string
    {
        if (! $host) {
            return 'Your host';
        }

        return $host->isAdmin() ? 'Vietstays team' : ($host->display_name ?: $host->name ?: 'Your host');
    }

    protected function initials(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('') ?: '?';
    }
}
