<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\HostCustomer;
use App\Models\HostCustomerMerge;
use App\Models\HostCustomerMessage;
use App\Models\HostCustomerMessageThread;
use App\Models\HostCustomerNote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CustomerAggregationService
{
    public function __construct(
        protected VvEmailService $emailService,
    ) {}

    private const AVATAR_COLORS = [
        '#12352b',
        '#1f7a44',
        '#b5651d',
        '#2f6d7a',
        '#7a4b8a',
        '#8a6d3b',
    ];

    private const COUNTRY_NAMES = [
        'NO' => 'Norway',
        'SE' => 'Sweden',
        'KR' => 'South Korea',
        'VN' => 'Vietnam',
        'DE' => 'Germany',
        'JP' => 'Japan',
        'CN' => 'China',
        'IN' => 'India',
        'IT' => 'Italy',
        'NL' => 'Netherlands',
        'FR' => 'France',
        'PL' => 'Poland',
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'AU' => 'Australia',
    ];

    public function scopedBookingsQuery(User $user): Builder
    {
        $query = Booking::query()
            ->with('apartment')
            ->whereNotNull('check_in_date')
            ->whereNotNull('check_out_date')
            ->where(function (Builder $builder) {
                $builder->where(function (Builder $guest) {
                    $guest->whereNotNull('email')
                        ->where('email', '!=', '');
                })->orWhere(function (Builder $guest) {
                    $guest->where('firstname', '!=', '')
                        ->orWhere('lastname', '!=', '');
                });
            });

        if ($user->isOperator() && ! $user->isAdmin()) {
            $query->whereIn('apartment_id', function ($subquery) use ($user) {
                $subquery->select('ID')
                    ->from('vv_apartments')
                    ->where('user_id', $user->legacy_wp_id);
            });
        }

        return $query;
    }

    public function countCustomers(User $user): int
    {
        return count($this->buildCustomerList($user));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listCustomers(User $user): array
    {
        return array_map(function (array $customer) {
            unset($customer['bookings']);

            return $customer;
        }, $this->buildCustomerList($user));
    }

    public function findCustomer(User $user, string $id): ?array
    {
        $id = $this->resolveMergeMap($user)[$id] ?? $id;

        foreach ($this->buildCustomerList($user) as $customer) {
            if ($customer['id'] === $id) {
                $customer['notes'] = array_merge(
                    $this->persistedNotes($user, $id),
                    $customer['notes'] ?? [],
                );

                return $customer;
            }
        }

        return null;
    }

    /**
     * @return array<string, string> merged_key => keep_key, already flattened.
     */
    public function resolveMergeMap(User $user): array
    {
        return HostCustomerMerge::query()
            ->where('user_id', $user->id)
            ->pluck('keep_key', 'merged_key')
            ->all();
    }

    /**
     * Merge $duplicateId into $keepId so they present as a single customer.
     *
     * @return array<string, mixed>
     */
    public function mergeCustomers(User $user, string $keepId, string $duplicateId): array
    {
        $map = $this->resolveMergeMap($user);
        $keepRoot = $map[$keepId] ?? $keepId;
        $duplicateRoot = $map[$duplicateId] ?? $duplicateId;

        if ($keepRoot === $duplicateRoot) {
            throw new \InvalidArgumentException('These customers are already merged.');
        }

        if (! $this->findCustomer($user, $keepRoot) || ! $this->findCustomer($user, $duplicateRoot)) {
            throw new \InvalidArgumentException('Customer not found.');
        }

        // Flatten: anything previously merged into the duplicate now points
        // straight at the new root, so lookups never need more than one hop.
        HostCustomerMerge::query()
            ->where('user_id', $user->id)
            ->where('keep_key', $duplicateRoot)
            ->update(['keep_key' => $keepRoot]);

        HostCustomerMerge::query()->updateOrCreate(
            ['user_id' => $user->id, 'merged_key' => $duplicateRoot],
            ['keep_key' => $keepRoot],
        );

        HostCustomerNote::query()
            ->where('user_id', $user->id)
            ->where('customer_key', $duplicateRoot)
            ->update(['customer_key' => $keepRoot]);

        $this->reKeyMessageThread($user, $duplicateRoot, $keepRoot);

        return $this->findCustomer($user, $keepRoot) ?? [];
    }

    /**
     * Move a message thread from the duplicate customer key onto the surviving
     * key. Threads are unique per [user_id, customer_key], so if the surviving
     * key already has a thread, fold the duplicate's messages into it instead
     * of a plain re-key (which would violate the unique index).
     */
    protected function reKeyMessageThread(User $user, string $duplicateRoot, string $keepRoot): void
    {
        $duplicateThread = HostCustomerMessageThread::query()
            ->where('user_id', $user->id)
            ->where('customer_key', $duplicateRoot)
            ->first();

        if (! $duplicateThread) {
            return;
        }

        $keepThread = HostCustomerMessageThread::query()
            ->where('user_id', $user->id)
            ->where('customer_key', $keepRoot)
            ->first();

        if (! $keepThread) {
            $duplicateThread->update(['customer_key' => $keepRoot]);

            return;
        }

        HostCustomerMessage::query()
            ->where('thread_id', $duplicateThread->id)
            ->update(['thread_id' => $keepThread->id]);

        $newestMessageAt = collect([$keepThread->last_message_at, $duplicateThread->last_message_at])
            ->filter()
            ->max();

        $keepThread->update(['last_message_at' => $newestMessageAt]);
        $duplicateThread->delete();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function persistedNotes(User $user, string $customerId): array
    {
        return HostCustomerNote::query()
            ->where('user_id', $user->id)
            ->where('customer_key', $customerId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (HostCustomerNote $note) => [
                'who' => $note->author ?: $user->name,
                'when' => $note->created_at->format('j M Y'),
                'text' => $note->text,
            ])
            ->all();
    }

    public function addNote(User $user, string $customerId, string $text): array
    {
        if (! $this->findCustomer($user, $customerId)) {
            throw new \InvalidArgumentException('Customer not found.');
        }

        HostCustomerNote::query()->create([
            'user_id' => $user->id,
            'customer_key' => $customerId,
            'author' => $user->name,
            'text' => $text,
        ]);

        return $this->findCustomer($user, $customerId) ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function threadSummary(User $user, string $customerKey): array
    {
        $thread = $this->threadForUser($user, $customerKey);

        if (! $thread) {
            return [
                'hasThread' => false,
                'unreadCount' => 0,
                'lastMessage' => null,
                'lastMessageAt' => null,
            ];
        }

        $lastMessage = HostCustomerMessage::query()
            ->where('thread_id', $thread->id)
            ->orderByDesc('created_at')
            ->first();

        return [
            'hasThread' => true,
            'unreadCount' => $this->unreadCount($thread),
            'lastMessage' => $lastMessage?->body,
            'lastMessageAt' => $thread->last_message_at?->toIso8601String(),
        ];
    }

    /**
     * Poll target: full message list for a thread. Marks the thread read by
     * the host as a side effect, since opening/polling a thread is how a host
     * "reads" it in this UI.
     *
     * @return array<string, mixed>
     */
    public function threadMessages(User $user, string $customerKey): array
    {
        $thread = $this->threadForUser($user, $customerKey);

        if (! $thread) {
            return [
                'messages' => [],
                'guestLink' => null,
                'lastMessageAt' => null,
            ];
        }

        $messages = HostCustomerMessage::query()
            ->where('thread_id', $thread->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (HostCustomerMessage $message) => $this->presentMessage($message))
            ->values()
            ->all();

        $thread->update(['host_last_read_at' => now()]);

        return [
            'messages' => $messages,
            'guestLink' => $this->guestThreadLink($thread),
            'lastMessageAt' => $thread->last_message_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function sendHostMessage(User $user, string $customerKey, string $body, ?int $bookingId = null): array
    {
        if (! $this->findCustomer($user, $customerKey)) {
            throw new \InvalidArgumentException('Customer not found.');
        }

        $thread = $this->findOrCreateThread($user, $customerKey);

        HostCustomerMessage::query()->create([
            'thread_id' => $thread->id,
            'booking_id' => $bookingId,
            'sender' => 'host',
            'author' => $user->name,
            'body' => $body,
        ]);

        $thread->update(['last_message_at' => now(), 'host_last_read_at' => now()]);

        $this->notifyGuestOfMessage($user, $thread, $body);

        return $this->findCustomer($user, $customerKey) ?? [];
    }

    /**
     * All message threads for a host, ordered with an upcoming stay first
     * (soonest check-in first), then threads with no upcoming stay ordered by
     * most recent past checkout.
     *
     * @return array<int, array<string, mixed>>
     */
    public function hostThreadList(User $user): array
    {
        $customersById = [];

        foreach ($this->buildCustomerList($user) as $customer) {
            $customersById[$customer['id']] = $customer;
        }

        $rows = [];

        foreach (HostCustomerMessageThread::query()->where('user_id', $user->id)->get() as $thread) {
            $customer = $customersById[$thread->customer_key] ?? null;

            $lastMessage = HostCustomerMessage::query()
                ->where('thread_id', $thread->id)
                ->orderByDesc('created_at')
                ->first();

            $rows[] = [
                'customerId' => $thread->customer_key,
                'name' => $customer['name'] ?? 'Guest',
                'initials' => $customer['initials'] ?? '?',
                'bg' => $customer['bg'] ?? '#8a9187',
                'nextLabel' => $customer['nextLabel'] ?? '—',
                'nextApt' => $customer['nextApt'] ?? '',
                'nextAptCode' => $customer['nextAptCode'] ?? '',
                'hasUpcoming' => (bool) ($customer['hasUpcoming'] ?? false) || (bool) ($customer['nextActive'] ?? false),
                'nextCheckInAt' => $customer['nextCheckInAt'] ?? null,
                'lastCheckoutAt' => $customer['lastCheckoutAt'] ?? null,
                'unreadCount' => $this->unreadCount($thread),
                'lastMessage' => $lastMessage?->body,
                'lastMessageAt' => $thread->last_message_at?->toIso8601String(),
            ];
        }

        usort($rows, function (array $a, array $b) {
            if ($a['hasUpcoming'] !== $b['hasUpcoming']) {
                return $b['hasUpcoming'] <=> $a['hasUpcoming'];
            }

            if ($a['hasUpcoming']) {
                return strcmp((string) $a['nextCheckInAt'], (string) $b['nextCheckInAt']);
            }

            return strcmp((string) $b['lastCheckoutAt'], (string) $a['lastCheckoutAt']);
        });

        return $rows;
    }

    /**
     * Public (guest, unauthenticated) lookup by magic-link token.
     *
     * @return array<string, mixed>|null
     */
    public function guestThread(string $token): ?array
    {
        $thread = HostCustomerMessageThread::query()->where('guest_token', $token)->first();

        if (! $thread) {
            return null;
        }

        $host = User::query()->find($thread->user_id);

        if (! $host) {
            return null;
        }

        $messages = HostCustomerMessage::query()
            ->where('thread_id', $thread->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (HostCustomerMessage $message) => $this->presentMessage($message))
            ->values()
            ->all();

        $customer = $this->findCustomer($host, $thread->customer_key);
        $bookings = collect($customer['bookings'] ?? [])->take(5)->values()->all();

        return [
            'hostName' => $host->name ?: 'Your host',
            'messages' => $messages,
            'bookings' => $bookings,
            'lastMessageAt' => $thread->last_message_at?->toIso8601String(),
        ];
    }

    public function postGuestMessage(string $token, string $body): ?array
    {
        $thread = HostCustomerMessageThread::query()->where('guest_token', $token)->first();

        if (! $thread) {
            return null;
        }

        HostCustomerMessage::query()->create([
            'thread_id' => $thread->id,
            'sender' => 'guest',
            'body' => $body,
        ]);

        $thread->update(['last_message_at' => now()]);

        return $this->guestThread($token);
    }

    protected function threadForUser(User $user, string $customerKey): ?HostCustomerMessageThread
    {
        return HostCustomerMessageThread::query()
            ->where('user_id', $user->id)
            ->where('customer_key', $customerKey)
            ->first();
    }

    protected function findOrCreateThread(User $user, string $customerKey): HostCustomerMessageThread
    {
        return HostCustomerMessageThread::query()->firstOrCreate(
            ['user_id' => $user->id, 'customer_key' => $customerKey],
            ['guest_token' => Str::random(48)],
        );
    }

    protected function unreadCount(HostCustomerMessageThread $thread): int
    {
        return HostCustomerMessage::query()
            ->where('thread_id', $thread->id)
            ->where('sender', 'guest')
            // Format explicitly to a microsecond-precision string: passing the
            // Carbon instance straight to where() lets Laravel's query grammar
            // re-stringify it with the connection's default (whole-second)
            // format, silently truncating the precision this comparison
            // depends on.
            ->when(
                $thread->host_last_read_at,
                fn (Builder $query) => $query->where('created_at', '>', $thread->host_last_read_at->format('Y-m-d H:i:s.u')),
            )
            ->count();
    }

    protected function presentMessage(HostCustomerMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender' => $message->sender,
            'author' => $message->author,
            'body' => $message->body,
            'bookingId' => $message->booking_id,
            'sentAt' => $message->created_at->toIso8601String(),
        ];
    }

    protected function notifyGuestOfMessage(User $user, HostCustomerMessageThread $thread, string $body): void
    {
        $customer = $this->findCustomer($user, $thread->customer_key);
        $email = (string) ($customer['rawEmail'] ?? '');

        if (blank($email)) {
            return;
        }

        $this->emailService->sendByCode('guest_message', $email, [
            'HOST_NAME' => (string) ($user->name ?: 'Your host'),
            'MESSAGE_PREVIEW' => Str::limit($body, 140),
            'GUEST_LINK' => $this->guestThreadLink($thread),
        ]);
    }

    protected function guestThreadLink(HostCustomerMessageThread $thread): string
    {
        $base = rtrim((string) config('app.url'), '/');

        return $base.'/messages/'.$thread->guest_token;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createManualCustomer(User $user, array $data): array
    {
        $email = filled($data['email'] ?? null) ? strtolower(trim((string) $data['email'])) : null;
        $country = filled($data['country'] ?? null) ? trim((string) $data['country']) : null;
        $countryCode = $this->countryCodeFromName($country);

        $record = HostCustomer::query()->create([
            'user_id' => $user->id,
            'legacy_host_id' => $user->legacy_wp_id,
            'name' => trim((string) $data['name']),
            'email' => $email,
            'phone' => filled($data['phone'] ?? null) ? trim((string) $data['phone']) : null,
            'country' => $country,
            'country_code' => $countryCode,
            'reserved_by' => filled($data['reserved_by'] ?? null) ? trim((string) $data['reserved_by']) : null,
            'note' => filled($data['note'] ?? null) ? trim((string) $data['note']) : null,
            'temp_ref' => $this->nextTempRef($user),
        ]);

        return $this->presentHostCustomer($record, validEmail: $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildCustomerList(User $user): array
    {
        $mergeMap = $this->resolveMergeMap($user);

        $customers = $this->aggregateCustomers(
            $this->scopedBookingsQuery($user)->orderByDesc('check_in_date')->get(),
            $mergeMap,
        );

        return $this->mergeManualCustomers($user, $customers, $mergeMap);
    }

    /**
     * @param  array<int, array<string, mixed>>  $customers
     * @param  array<string, string>  $mergeMap
     * @return array<int, array<string, mixed>>
     */
    protected function mergeManualCustomers(User $user, array $customers, array $mergeMap = []): array
    {
        $indexed = [];

        foreach ($customers as $customer) {
            $indexed[$customer['id']] = $customer;
        }

        $emailToId = [];

        foreach ($customers as $customer) {
            $email = strtolower(trim((string) ($customer['rawEmail'] ?? '')));

            if ($email !== '') {
                $emailToId[$email] = $customer['id'];
            }
        }

        foreach ($this->scopedManualCustomersQuery($user)->orderByDesc('created_at')->get() as $record) {
            $email = strtolower(trim((string) ($record->email ?? '')));

            if ($email !== '' && isset($emailToId[$email])) {
                $existingId = $emailToId[$email];

                if ($record->note) {
                    $indexed[$existingId]['notes'][] = [
                        'who' => $record->reserved_by ?: 'You',
                        'when' => $record->created_at->format('j M Y'),
                        'text' => $record->note,
                    ];
                }

                continue;
            }

            $presented = $this->presentHostCustomer(
                $record,
                validEmail: $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL),
            );

            $targetId = $mergeMap[$presented['id']] ?? $presented['id'];
            $presented['id'] = $targetId;

            if (! isset($indexed[$targetId])) {
                $indexed[$targetId] = $presented;

                continue;
            }

            // Target already has an identity (a booking-derived group, or a
            // previously processed record merged into the same customer) —
            // fold this record's note/tags in rather than clobbering it.
            if ($record->note) {
                $indexed[$targetId]['notes'][] = [
                    'who' => $record->reserved_by ?: 'You',
                    'when' => $record->created_at->format('j M Y'),
                    'text' => $record->note,
                ];
            }

            $indexed[$targetId]['tags'] = array_values(array_unique(
                array_merge($indexed[$targetId]['tags'], $presented['tags'])
            ));
        }

        $merged = array_values($indexed);

        usort($merged, fn (array $a, array $b) => $b['stays'] <=> $a['stays'] ?: strcmp($a['name'], $b['name']));

        return $merged;
    }

    protected function scopedManualCustomersQuery(User $user): \Illuminate\Database\Eloquent\Builder
    {
        $query = HostCustomer::query()->where('user_id', $user->id);

        if ($user->isOperator() && ! $user->isAdmin()) {
            $query->where('legacy_host_id', $user->legacy_wp_id);
        }

        return $query;
    }

    protected function presentHostCustomer(HostCustomer $record, bool $validEmail): array
    {
        $today = now()->startOfDay();
        $colorIndex = abs(crc32('host:'.$record->id)) % count(self::AVATAR_COLORS);
        $country = $record->country ?: (self::COUNTRY_NAMES[$record->country_code] ?? '—');
        $flag = $record->country_code ?: $this->countryCodeFromName($record->country);
        $notes = [];

        if ($record->note) {
            $notes[] = [
                'who' => $record->reserved_by ?: 'You',
                'when' => $record->created_at->format('j M Y'),
                'text' => $record->note,
            ];
        }

        $emailDisplay = $validEmail
            ? (string) $record->email
            : ('Temporary #'.$record->temp_ref.' · created by '.($record->reserved_by ?: 'you'));

        return [
            'id' => $this->manualCustomerKey($record),
            'name' => $record->name,
            'email' => $emailDisplay,
            'rawEmail' => $validEmail ? (string) $record->email : '',
            'phone' => $record->phone ?: '—',
            'flag' => $flag,
            'country' => $country !== '—' ? $country : '—',
            'lang' => '—',
            'member' => $record->created_at->format('M Y'),
            'status' => 'No booking',
            'segment' => $validEmail ? 'New customer' : 'Temporary',
            'stays' => 0,
            'nights' => 0,
            'revenue' => 0.0,
            'rating' => null,
            'nextLabel' => '—',
            'nextApt' => '',
            'nextAptCode' => '',
            'nextActive' => false,
            'nextSoon' => false,
            'hasUpcoming' => false,
            'nextCheckInAt' => null,
            'lastCheckoutAt' => null,
            'needsAction' => true,
            'longestNights' => 0,
            'avgNights' => 0,
            'initials' => $this->initials($record->name),
            'bg' => self::AVATAR_COLORS[$colorIndex],
            'tags' => $validEmail ? ['Vietstays direct'] : ['Temporary account'],
            'consent' => false,
            'prefs' => [],
            'notes' => $notes,
            'docs' => [],
            'reviews' => [],
            'bookings' => [],
            'manual' => true,
            'tempRef' => $record->temp_ref,
            'reservedBy' => $record->reserved_by,
            'createdAt' => $record->created_at->toIso8601String(),
        ];
    }

    protected function manualCustomerKey(HostCustomer $record): string
    {
        return hash('sha256', 'host:'.$record->id);
    }

    protected function nextTempRef(User $user): int
    {
        $max = (int) HostCustomer::query()
            ->where('user_id', $user->id)
            ->max('temp_ref');

        return max(2042, $max + 1);
    }

    protected function countryCodeFromName(?string $country): ?string
    {
        if (! $country) {
            return null;
        }

        $normalized = Str::lower(trim($country));

        foreach (self::COUNTRY_NAMES as $code => $name) {
            if (Str::lower($name) === $normalized) {
                return $code;
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @return array<int, array<string, mixed>>
     */
    /**
     * @param  array<string, string>  $mergeMap
     */
    protected function aggregateCustomers(Collection $bookings, array $mergeMap = []): array
    {
        $today = now()->startOfDay();
        $groups = [];

        foreach ($bookings as $booking) {
            $guestName = trim($booking->firstname.' '.$booking->lastname);

            if ($guestName === '' && blank($booking->email)) {
                continue;
            }

            if ($this->isBlockedGuest($guestName)) {
                continue;
            }

            $key = $this->customerKey($booking, $guestName);
            $key = $mergeMap[$key] ?? $key;
            $extra = is_array($booking->extra_data) ? $booking->extra_data : [];
            $nights = max(1, (int) $booking->check_in_date->diffInDays($booking->check_out_date));
            $cancelled = $booking->status === 'cancelled';
            $category = $this->bookingCategory($booking, $today);
            $apartmentName = $booking->apartment?->display_name ?: $booking->apartment?->name ?: '';
            $channel = $this->channelLabel($booking, $extra);

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'key' => $key,
                    'name' => $guestName !== '' ? $guestName : ($booking->email ?: 'Guest'),
                    'email' => (string) $booking->email,
                    'phone' => (string) ($extra['phone'] ?? ''),
                    'flag' => strtoupper((string) ($extra['country_code'] ?? '')),
                    'member' => $booking->dateadded,
                    'bookings' => [],
                    'stays' => 0,
                    'nights' => 0,
                    'revenue' => 0.0,
                    'has_upcoming' => false,
                    'has_active' => false,
                    'needs_action' => false,
                    'next_booking' => null,
                    'active_booking' => null,
                    'longest_nights' => 0,
                    'last_checkout' => null,
                ];
            }

            $entry = [
                'id' => 'BK-'.$booking->ID,
                'apartment' => $apartmentName,
                'apartment_code' => $this->apartmentCode($apartmentName),
                'dates' => $booking->check_in_date->format('j M').' – '.$booking->check_out_date->format('j M Y'),
                'nights' => $nights,
                'amount' => $this->formatVnd((float) $booking->total),
                'channel' => $channel,
                'status' => $this->bookingStatusLabel($booking, $today),
                'category' => $category,
                'check_in' => $booking->check_in_date->copy(),
                'check_out' => $booking->check_out_date->copy(),
                'cancelled' => $cancelled,
            ];

            $groups[$key]['bookings'][] = $entry;

            if (! $cancelled) {
                $groups[$key]['stays']++;
                $groups[$key]['nights'] += $nights;
                $groups[$key]['revenue'] += (float) $booking->total;
                $groups[$key]['longest_nights'] = max($groups[$key]['longest_nights'], $nights);
            }

            if ($booking->dateadded && (
                ! $groups[$key]['member'] || $booking->dateadded->lt($groups[$key]['member'])
            )) {
                $groups[$key]['member'] = $booking->dateadded;
            }

            if ($guestName !== '' && Str::length($groups[$key]['name']) < Str::length($guestName)) {
                $groups[$key]['name'] = $guestName;
            }

            if (filled($booking->email)) {
                $groups[$key]['email'] = (string) $booking->email;
            }

            if (filled($extra['phone'] ?? null)) {
                $groups[$key]['phone'] = (string) $extra['phone'];
            }

            if (filled($extra['country_code'] ?? null)) {
                $groups[$key]['flag'] = strtoupper((string) $extra['country_code']);
            }

            if ($category === 'active') {
                $groups[$key]['has_active'] = true;
                $groups[$key]['active_booking'] = $entry;
            }

            if ($category === 'upcoming' && ! $cancelled) {
                $groups[$key]['has_upcoming'] = true;

                if (
                    ! $groups[$key]['next_booking']
                    || $entry['check_in']->lt($groups[$key]['next_booking']['check_in'])
                ) {
                    $groups[$key]['next_booking'] = $entry;
                }
            }

            if ($booking->status === 'pending' || (
                $category === 'upcoming'
                && ! $cancelled
                && $today->diffInDays($entry['check_in'], false) <= 3
            )) {
                $groups[$key]['needs_action'] = true;
            }

            if ($category === 'past' && ! $cancelled) {
                $checkout = $entry['check_out']->copy()->startOfDay();

                if (! $groups[$key]['last_checkout'] || $checkout->gt($groups[$key]['last_checkout'])) {
                    $groups[$key]['last_checkout'] = $checkout;
                }
            }
        }

        $customers = [];

        foreach ($groups as $group) {
            if ($group['stays'] === 0 && empty(array_filter($group['bookings'], fn (array $b) => ! $b['cancelled']))) {
                continue;
            }

            $customers[] = $this->presentCustomer($group, $today);
        }

        usort($customers, fn (array $a, array $b) => $b['stays'] <=> $a['stays'] ?: strcmp($a['name'], $b['name']));

        return $customers;
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<string, mixed>
     */
    protected function presentCustomer(array $group, Carbon $today): array
    {
        $stays = (int) $group['stays'];
        $nights = (int) $group['nights'];
        $avgNights = $stays > 0 ? (int) round($nights / $stays) : 0;
        $name = (string) $group['name'];
        $email = (string) $group['email'];
        $flag = (string) $group['flag'];
        $country = self::COUNTRY_NAMES[$flag] ?? ($flag !== '' ? $flag : 'Vietnam');
        $status = $this->customerStatus($group);
        $segment = $this->customerSegment($stays, $group['last_checkout'], $today, blank($email));
        $next = $this->nextStayPresentation($group, $today);
        $colorIndex = abs(crc32($group['key'])) % count(self::AVATAR_COLORS);

        $bookings = collect($group['bookings'])
            ->sortByDesc(fn (array $booking) => $booking['check_in']->timestamp)
            ->map(function (array $booking) {
                unset($booking['check_in'], $booking['check_out'], $booking['cancelled'], $booking['category']);

                return $booking;
            })
            ->values()
            ->all();

        return [
            'id' => $group['key'],
            'name' => $name,
            'email' => $email !== '' ? $email : 'Unregistered · no email',
            'rawEmail' => $email,
            'phone' => $group['phone'] !== '' ? $group['phone'] : '—',
            'flag' => $flag,
            'country' => $country,
            'lang' => '—',
            'member' => $group['member'] instanceof Carbon
                ? $group['member']->format('M Y')
                : '—',
            'status' => $status,
            'segment' => $segment,
            'stays' => $stays,
            'nights' => $nights,
            'revenue' => round((float) $group['revenue'], 2),
            'rating' => null,
            'nextLabel' => $next['label'],
            'nextApt' => $next['apartment'],
            'nextAptCode' => $next['code'],
            'nextActive' => $group['has_active'],
            'nextSoon' => $next['soon'],
            'hasUpcoming' => $group['has_upcoming'],
            'nextCheckInAt' => $group['active_booking']
                ? $today->toIso8601String()
                : ($group['next_booking'] ? $group['next_booking']['check_in']->toIso8601String() : null),
            'lastCheckoutAt' => $group['last_checkout']?->toIso8601String(),
            'needsAction' => $group['needs_action'],
            'longestNights' => (int) $group['longest_nights'],
            'avgNights' => $avgNights,
            'initials' => $this->initials($name),
            'bg' => self::AVATAR_COLORS[$colorIndex],
            'tags' => [],
            'consent' => false,
            'prefs' => [],
            'notes' => [],
            'docs' => [],
            'reviews' => [],
            'bookings' => $bookings,
        ];
    }

    protected function customerKey(Booking $booking, string $guestName): string
    {
        $email = strtolower(trim((string) $booking->email));

        if ($email !== '') {
            return hash('sha256', 'email:'.$email);
        }

        $extra = is_array($booking->extra_data) ? $booking->extra_data : [];
        $phone = preg_replace('/\D+/', '', (string) ($extra['phone'] ?? '')) ?: 'unknown';

        return hash('sha256', 'guest:'.Str::lower($guestName).':'.$phone);
    }

    protected function isBlockedGuest(string $guestName): bool
    {
        $normalized = Str::lower($guestName);

        return Str::contains($normalized, ['blokkert', 'blocked', '[airbnb']);
    }

    protected function bookingCategory(Booking $booking, Carbon $today): string
    {
        if ($booking->status === 'cancelled') {
            return 'cancelled';
        }

        $checkIn = $booking->check_in_date->copy()->startOfDay();
        $checkOut = $booking->check_out_date->copy()->startOfDay();

        if ($today->gte($checkIn) && $today->lt($checkOut)) {
            return 'active';
        }

        if ($today->lt($checkIn)) {
            return 'upcoming';
        }

        return 'past';
    }

    protected function bookingStatusLabel(Booking $booking, Carbon $today): string
    {
        if ($booking->status === 'cancelled') {
            return 'Cancelled';
        }

        if ($booking->status === 'pending') {
            return 'New booking';
        }

        return match ($this->bookingCategory($booking, $today)) {
            'active' => 'In progress',
            'upcoming' => 'Upcoming',
            default => 'Completed',
        };
    }

    /**
     * @param  array<string, mixed>  $group
     */
    protected function customerStatus(array $group): string
    {
        if ($group['has_active']) {
            return 'Staying now';
        }

        if ($group['has_upcoming']) {
            return 'Upcoming';
        }

        return 'Past';
    }

    protected function customerSegment(int $stays, ?Carbon $lastCheckout, Carbon $today, bool $unregistered): string
    {
        if ($unregistered) {
            return 'Temporary';
        }

        if ($stays >= 4) {
            return 'VIP';
        }

        if ($stays >= 2) {
            return 'Returning';
        }

        if ($lastCheckout && $today->diffInDays($lastCheckout) <= 30) {
            return 'New customer';
        }

        return $stays === 1 ? 'New customer' : 'Customer';
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array{label: string, apartment: string, code: string, soon: bool}
     */
    protected function nextStayPresentation(array $group, Carbon $today): array
    {
        if ($group['active_booking']) {
            return [
                'label' => 'In progress',
                'apartment' => $group['active_booking']['apartment'],
                'code' => $group['active_booking']['apartment_code'],
                'soon' => true,
            ];
        }

        if (! $group['next_booking']) {
            return [
                'label' => '—',
                'apartment' => '',
                'code' => '',
                'soon' => false,
            ];
        }

        $days = (int) $today->diffInDays($group['next_booking']['check_in']->copy()->startOfDay(), false);
        $label = $group['next_booking']['check_in']->format('j M Y');

        if ($days === 0) {
            $label .= ' (today)';
        } elseif ($days === 1) {
            $label .= ' (tomorrow)';
        } elseif ($days > 1 && $days <= 14) {
            $label .= ' (in '.$days.' d)';
        }

        return [
            'label' => $label,
            'apartment' => $group['next_booking']['apartment'],
            'code' => $group['next_booking']['apartment_code'],
            'soon' => $days >= 0 && $days <= 7,
        ];
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected function channelLabel(Booking $booking, array $extra): string
    {
        $channel = strtolower((string) ($extra['source'] ?? 'vietstays'));

        return match (true) {
            str_contains($channel, 'airbnb') => 'Airbnb',
            in_array($channel, ['booking.com', 'trip.com', 'external'], true) => ucfirst($channel),
            default => filled($booking->promo_code)
                ? 'Vietstays · '.$booking->promo_code
                : 'Vietstays',
        };
    }

    protected function apartmentCode(string $apartmentName): string
    {
        if ($apartmentName === '') {
            return '';
        }

        $parts = preg_split('/\s+/', $apartmentName) ?: [];
        $prefix = mb_strtoupper(mb_substr($parts[0] ?? 'APT', 0, 3));
        $suffix = mb_strtoupper(mb_substr($parts[count($parts) - 1] ?? '01', 0, 2));

        return $prefix.'-'.$suffix;
    }

    protected function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return mb_strtoupper(collect($parts)->take(2)->map(fn (string $part) => mb_substr($part, 0, 1))->implode(''));
    }

    protected function formatVnd(float $amount): string
    {
        return number_format($amount, 0, '.', ',').' ₫';
    }
}
