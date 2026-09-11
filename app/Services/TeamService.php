<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\HostTeamInvitation;
use App\Models\HostTeamMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeamService
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
        '#43503f',
        '#6b5aa8',
        '#a35a4e',
    ];

    /**
     * @return array<string, mixed>
     */
    public function teamPayload(User $user, string $teamType): array
    {
        $this->ensureSeedData($user, $teamType);

        $members = $this->scopedMembersQuery($user, $teamType)
            ->orderBy('name')
            ->get()
            ->map(fn (HostTeamMember $member) => $this->presentMember($member))
            ->values()
            ->all();

        $invitations = $this->scopedInvitationsQuery($user, $teamType)
            ->orderByDesc('sent_at')
            ->get()
            ->map(fn (HostTeamInvitation $invite) => $this->presentInvitation($invite))
            ->values()
            ->all();

        return [
            'members' => $members,
            'invitations' => $invitations,
            'stats' => $this->buildStats($members, $teamType),
            'areas' => $this->areaOptions($members),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createInvitation(User $user, string $teamType, array $data): array
    {
        $email = filled($data['email'] ?? null) ? strtolower(trim((string) $data['email'])) : null;
        $phone = filled($data['phone'] ?? null) ? trim((string) $data['phone']) : null;
        $name = filled($data['name'] ?? null)
            ? trim((string) $data['name'])
            : $this->nameFromContact($email, $phone);

        $record = HostTeamInvitation::query()->create([
            'user_id' => $user->id,
            'legacy_host_id' => $user->legacy_wp_id,
            'team_type' => $teamType,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => (string) $data['role'],
            'role_key' => filled($data['role_key'] ?? null) ? (string) $data['role_key'] : null,
            'area' => filled($data['area'] ?? null) ? trim((string) $data['area']) : null,
            'permissions' => $data['permissions'] ?? [],
            'pay_rate' => isset($data['pay_rate']) ? (int) $data['pay_rate'] : null,
            'pay_setup' => $data['pay_setup'] ?? null,
            'org' => filled($data['org'] ?? null) ? trim((string) $data['org']) : null,
            'token' => Str::random(48),
            'sent_at' => now(),
        ]);

        $this->sendInvitationEmail($user, $record);

        return $this->presentInvitation($record);
    }

    public function toggleGuestInfo(User $user, int $memberId, bool $enabled): ?array
    {
        $member = $this->scopedMembersQuery($user, 'operations')
            ->whereKey($memberId)
            ->first();

        if (! $member) {
            return null;
        }

        $member->update(['guest_info' => $enabled]);

        return $this->presentMember($member->fresh());
    }

    public function withdrawInvitation(User $user, int $invitationId): bool
    {
        return (bool) $this->scopedInvitationsQuery($user)
            ->whereKey($invitationId)
            ->delete();
    }

    public function remindInvitation(User $user, int $invitationId): ?array
    {
        $invite = $this->scopedInvitationsQuery($user)->whereKey($invitationId)->first();

        if (! $invite) {
            return null;
        }

        if (blank($invite->token)) {
            $invite->token = Str::random(48);
        }

        $invite->sent_at = now();
        $invite->save();

        $this->sendInvitationEmail($user, $invite);

        return $this->presentInvitation($invite->fresh());
    }

    public function invitationPreview(string $token): ?array
    {
        $invite = HostTeamInvitation::query()->where('token', $token)->first();

        if (! $invite) {
            return null;
        }

        $owner = User::query()->find($invite->user_id);

        return [
            'name' => $invite->name,
            'role' => $invite->role,
            'area' => $invite->area,
            'teamType' => $invite->team_type,
            'org' => $invite->org,
            'inviterName' => $owner?->name ?? 'Vietstays host',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function acceptInvitation(string $token): ?array
    {
        return DB::transaction(function () use ($token) {
            $invite = HostTeamInvitation::query()->where('token', $token)->lockForUpdate()->first();

            if (! $invite) {
                return null;
            }

            $attributes = $this->memberAttributesFromInvitation($invite);

            $member = HostTeamMember::query()->create([
                'user_id' => $invite->user_id,
                'legacy_host_id' => $invite->legacy_host_id,
                'team_type' => $invite->team_type,
                'name' => $invite->name,
                'email' => $invite->email,
                'phone' => $invite->phone,
                'org' => $invite->org,
                'area' => $invite->area,
                'permissions' => $invite->permissions,
                'status' => 'active',
                ...$attributes,
            ]);

            $invite->delete();

            return [
                'teamType' => $member->team_type,
                'member' => $this->presentMember($member),
            ];
        });
    }

    public function updateMemberStatus(User $user, int $memberId, string $status): ?array
    {
        $member = $this->scopedMembersQuery($user)->whereKey($memberId)->first();

        if (! $member) {
            return null;
        }

        $member->update(['status' => $status]);

        return $this->presentMember($member->fresh());
    }

    public function removeMember(User $user, int $memberId): bool
    {
        $member = $this->scopedMembersQuery($user)->whereKey($memberId)->first();

        if (! $member) {
            return false;
        }

        $member->apartments()->detach();
        $member->delete();

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    protected function memberAttributesFromInvitation(HostTeamInvitation $invite): array
    {
        if ($invite->team_type === 'sales') {
            $map = [
                'host_internal' => ['member_type' => 'host', 'link' => 'internal', 'func' => 'Host', 'pooled' => true, 'out_pct' => 0],
                'cohost_external' => ['member_type' => 'host', 'link' => 'external', 'func' => 'Co-host', 'pooled' => false, 'out_pct' => (int) ($invite->pay_rate ?? 0)],
                'host_agent' => ['member_type' => 'agent', 'link' => 'internal', 'func' => 'Host Agent', 'pooled' => false, 'out_pct' => (int) ($invite->pay_rate ?? 0)],
            ];

            return $map[$invite->role_key] ?? ['member_type' => 'host', 'link' => 'internal', 'func' => $invite->role, 'pooled' => $invite->pay_setup === 'pooled', 'out_pct' => (int) ($invite->pay_rate ?? 0)];
        }

        return [
            'roles' => $invite->role_key ? [$invite->role_key] : [],
        ];
    }

    protected function sendInvitationEmail(User $inviter, HostTeamInvitation $invite): void
    {
        if (blank($invite->email)) {
            return;
        }

        $tokens = [
            'NAME' => (string) $invite->name,
            'INVITER_NAME' => (string) ($inviter->name ?: 'Vietstays host'),
            'ROLE' => (string) $invite->role,
            'AREA_LINE' => filled($invite->area) ? " for {$invite->area}" : '',
            'ACCEPT_LINK' => $this->invitationAcceptLink($invite),
        ];

        $this->emailService->sendByCode('team_invitation', (string) $invite->email, $tokens);
    }

    protected function invitationAcceptLink(HostTeamInvitation $invite): string
    {
        $base = rtrim((string) config('app.url'), '/');

        return $base.'/team-invite/'.$invite->token;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function memberDetail(User $user, int $memberId): ?array
    {
        $member = $this->scopedMembersQuery($user)->whereKey($memberId)->first();

        if (! $member) {
            return null;
        }

        $apartmentIds = $this->memberApartmentIds($member);
        $bookings = $this->memberBookings($apartmentIds);
        $nonCancelled = $bookings->where('status', '!=', 'cancelled');

        $assignedApartments = $apartmentIds === []
            ? collect()
            : Apartment::query()->whereIn('ID', $apartmentIds)->get(['ID', 'name', 'display_name', 'rooms']);

        return [
            'member' => $this->presentMemberSummary($member, $apartmentIds, $bookings, $nonCancelled),
            'roomFilters' => $this->buildRoomFilters($assignedApartments, $bookings),
            'discountFilters' => $this->buildDiscountFilters($bookings),
            'assignedApartmentIds' => $apartmentIds,
            'bookings' => $bookings->map(fn (Booking $booking) => $this->presentMemberBooking($booking, $member))->values()->all(),
        ];
    }

    /**
     * @param  array<int, int>  $apartmentIds
     */
    public function assignApartments(User $user, int $memberId, array $apartmentIds): ?array
    {
        $member = $this->scopedMembersQuery($user)->whereKey($memberId)->first();

        if (! $member) {
            return null;
        }

        $ownedIds = Apartment::query()
            ->where('user_id', $user->legacy_wp_id)
            ->whereIn('ID', $apartmentIds)
            ->pluck('ID')
            ->all();

        $member->apartments()->sync($ownedIds);

        return $this->memberDetail($user, $memberId);
    }

    /**
     * @return array<int, int>
     */
    protected function memberApartmentIds(HostTeamMember $member): array
    {
        return $member->apartments()->pluck('vv_apartments.ID')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @param  array<int, int>  $apartmentIds
     */
    protected function memberBookings(array $apartmentIds): Collection
    {
        if ($apartmentIds === []) {
            return collect();
        }

        return Booking::query()
            ->with('apartment')
            ->whereIn('apartment_id', $apartmentIds)
            ->where('dateadded', '>=', now()->subDays(90))
            ->orderByDesc('check_in_date')
            ->get();
    }

    /**
     * @param  array<int, int>  $apartmentIds
     */
    protected function presentMemberSummary(HostTeamMember $member, array $apartmentIds, Collection $bookings, Collection $nonCancelled): array
    {
        $commissionPct = $this->effectiveCommissionPct($member);
        $gross = (float) $nonCancelled->sum('total');

        return [
            'id' => (string) $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'phone' => $member->phone,
            'org' => $member->org,
            'area' => $member->area,
            'func' => $member->func,
            'link' => $member->link,
            'type' => $member->member_type,
            'outPct' => $commissionPct,
            'pooled' => (bool) $member->pooled,
            'status' => $member->status,
            'bg' => $member->avatar_color ?: self::AVATAR_COLORS[abs(crc32($member->name)) % count(self::AVATAR_COLORS)],
            'apartments' => count($apartmentIds),
            'bookings90' => $bookings->count(),
            'gross90' => $gross,
            'commissionOwed90' => $commissionPct > 0 ? round($gross * $commissionPct / 100) : 0,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildRoomFilters(Collection $assignedApartments, Collection $bookings): array
    {
        $filters = [
            ['key' => 'all', 'label' => 'All', 'count' => $bookings->count()],
        ];

        $roomCounts = $assignedApartments->pluck('rooms')->filter()->unique()->sort()->values();

        foreach ($roomCounts as $rooms) {
            $apartmentIdsForRoom = $assignedApartments->where('rooms', $rooms)->pluck('ID')->all();

            $filters[] = [
                'key' => (string) $rooms,
                'label' => $rooms == 1 ? 'Studio' : "{$rooms}BR",
                'count' => $bookings->whereIn('apartment_id', $apartmentIdsForRoom)->count(),
            ];
        }

        return $filters;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildDiscountFilters(Collection $bookings): array
    {
        $withDiscount = $bookings->filter(fn (Booking $booking) => $this->hasDiscount($booking))->count();

        return [
            ['key' => 'all', 'label' => 'All', 'count' => $bookings->count()],
            ['key' => 'with', 'label' => 'With discount', 'count' => $withDiscount],
            ['key' => 'without', 'label' => 'Without discount', 'count' => $bookings->count() - $withDiscount],
        ];
    }

    protected function presentMemberBooking(Booking $booking, HostTeamMember $member): array
    {
        $apartment = $booking->apartment;
        $nights = max(1, (int) $booking->check_in_date->diffInDays($booking->check_out_date));
        $extra = is_array($booking->extra_data) ? $booking->extra_data : [];
        $commissionPct = $this->effectiveCommissionPct($member);
        $commission = $booking->status === 'cancelled'
            ? 0
            : round(((float) $booking->total) * $commissionPct / 100);

        return [
            'id' => $booking->ID,
            'bookingNum' => $booking->booking_num ?: ('BK-'.$booking->ID),
            'apartment' => $apartment?->display_name ?: $apartment?->name,
            'apartmentRooms' => (int) ($apartment?->rooms ?? 0),
            'guest' => trim($booking->firstname.' '.$booking->lastname) ?: (string) $booking->email,
            'checkIn' => $booking->check_in_date->format('Y-m-d'),
            'checkOut' => $booking->check_out_date->format('Y-m-d'),
            'nights' => $nights,
            'channel' => $this->bookingChannelLabel($booking, $extra),
            'amount' => (float) $booking->total,
            'commission' => $commission,
            'commissionPct' => $commissionPct,
            'hasDiscount' => $this->hasDiscount($booking),
            'status' => $booking->status,
        ];
    }

    protected function hasDiscount(Booking $booking): bool
    {
        return ((float) $booking->campaign_discount) > 0
            || ((float) $booking->basic_discount) > 0
            || filled($booking->promo_code);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected function bookingChannelLabel(Booking $booking, array $extra): string
    {
        $source = strtolower((string) ($extra['source'] ?? 'vietstays'));

        if (str_contains($source, 'airbnb')) {
            return 'Airbnb';
        }

        if (in_array($source, ['booking.com', 'trip.com', 'external'], true)) {
            return 'External booking';
        }

        if (filled($booking->promo_code)) {
            return 'Vietstays · '.$booking->promo_code;
        }

        return $source === 'manual' ? 'Manual entry' : 'Vietstays';
    }

    protected function effectiveCommissionPct(HostTeamMember $member): int
    {
        if ($member->pooled || $member->member_type === 'agent') {
            return 0;
        }

        return (int) $member->out_pct;
    }

    protected function scopedMembersQuery(User $user, ?string $teamType = null): Builder
    {
        $query = HostTeamMember::query()->where('user_id', $user->id);

        if ($user->isPartner() && ! $user->isAdmin()) {
            $query->where('legacy_host_id', $user->legacy_wp_id);
        }

        if ($teamType) {
            $query->where('team_type', $teamType);
        }

        return $query;
    }

    protected function scopedInvitationsQuery(User $user, ?string $teamType = null): Builder
    {
        $query = HostTeamInvitation::query()->where('user_id', $user->id);

        if ($user->isPartner() && ! $user->isAdmin()) {
            $query->where('legacy_host_id', $user->legacy_wp_id);
        }

        if ($teamType) {
            $query->where('team_type', $teamType);
        }

        return $query;
    }

    protected function ensureSeedData(User $user, string $teamType): void
    {
        if ($this->scopedMembersQuery($user, $teamType)->exists()) {
            return;
        }

        foreach ($this->defaultMembers($teamType) as $row) {
            HostTeamMember::query()->create([
                'user_id' => $user->id,
                'legacy_host_id' => $user->legacy_wp_id,
                ...$row,
            ]);
        }

        if (! $this->scopedInvitationsQuery($user, $teamType)->exists()) {
            foreach ($this->defaultInvitations($teamType) as $row) {
                $sentDaysAgo = (int) ($row['sent_days_ago'] ?? 0);
                unset($row['sent_days_ago'], $row['avatar_color']);

                HostTeamInvitation::query()->create([
                    'user_id' => $user->id,
                    'legacy_host_id' => $user->legacy_wp_id,
                    'sent_at' => now()->subDays($sentDaysAgo),
                    ...$row,
                ]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function defaultMembers(string $teamType): array
    {
        if ($teamType === 'sales') {
            return [
                ['team_type' => 'sales', 'name' => 'Mai Nguyen', 'email' => 'mai@vietstay.vn', 'org' => 'VietStay Saigon', 'area' => 'Thảo Điền', 'func' => 'Host', 'link' => 'internal', 'member_type' => 'host', 'apartments' => 5, 'bookings90' => 42, 'gross90' => 486_000_000, 'out_pct' => 0, 'pooled' => true, 'rating' => 4.9, 'status' => 'active', 'avatar_color' => '#12352b'],
                ['team_type' => 'sales', 'name' => 'Trang Le', 'email' => 'trang@vietstay.vn', 'org' => 'VietStay Saigon', 'area' => 'Bình Thạnh', 'func' => 'Host', 'link' => 'internal', 'member_type' => 'host', 'apartments' => 4, 'bookings90' => 33, 'gross90' => 371_000_000, 'out_pct' => 0, 'pooled' => true, 'rating' => 4.7, 'status' => 'active', 'avatar_color' => '#b5651d'],
                ['team_type' => 'sales', 'name' => 'Linh Dao', 'email' => 'linh@vietstay.vn', 'org' => 'VietStay Saigon', 'area' => 'Thủ Thiêm', 'func' => 'Host', 'link' => 'internal', 'member_type' => 'host', 'apartments' => 2, 'bookings90' => 19, 'gross90' => 224_000_000, 'out_pct' => 0, 'pooled' => true, 'rating' => 5.0, 'status' => 'active', 'avatar_color' => '#43503f'],
                ['team_type' => 'sales', 'name' => 'Peter Nguyen', 'email' => 'peter@indochine.vn', 'org' => 'Indochine Living', 'area' => 'Thảo Điền', 'func' => 'Co-host', 'link' => 'external', 'member_type' => 'host', 'apartments' => 6, 'bookings90' => 51, 'gross90' => 210_000_000, 'out_pct' => 12, 'pooled' => false, 'rating' => 4.6, 'status' => 'active', 'avatar_color' => '#2f6d7a'],
                ['team_type' => 'sales', 'name' => 'Ha Vu', 'email' => 'ha@saigonstay.co', 'org' => 'Saigon Stay Co.', 'area' => 'District 1', 'func' => 'Co-host', 'link' => 'external', 'member_type' => 'host', 'apartments' => 3, 'bookings90' => 26, 'gross90' => 288_000_000, 'out_pct' => 15, 'pooled' => false, 'rating' => 4.3, 'status' => 'active', 'avatar_color' => '#8a6d3b'],
                ['team_type' => 'sales', 'name' => 'Anh Vo', 'email' => 'anh@vietstay.vn', 'org' => 'VietStay Saigon', 'area' => 'District 1', 'func' => 'Host Agent', 'link' => 'internal', 'member_type' => 'agent', 'apartments' => 3, 'bookings90' => 14, 'gross90' => 162_000_000, 'out_pct' => 8, 'pooled' => false, 'rating' => 4.4, 'status' => 'active', 'avatar_color' => '#6b5aa8'],
                ['team_type' => 'sales', 'name' => 'Kim Bui', 'email' => 'kim@saigonstay.co', 'org' => 'Saigon Stay Co.', 'area' => 'Thảo Điền', 'func' => 'Host Agent', 'link' => 'external', 'member_type' => 'agent', 'apartments' => 0, 'bookings90' => 4, 'gross90' => 47_000_000, 'out_pct' => 8, 'pooled' => false, 'rating' => 3.9, 'status' => 'paused', 'avatar_color' => '#a35a4e'],
            ];
        }

        return [
            ['team_type' => 'operations', 'name' => 'Duc Tran', 'phone' => '+84 91 220 7734', 'roles' => ['cleaning', 'keys'], 'area' => 'All buildings', 'tasks_week' => 18, 'avg_time' => '32 min', 'avg_time_warn' => false, 'guest_info' => true, 'status' => 'active', 'avatar_color' => '#1f7a44'],
            ['team_type' => 'operations', 'name' => 'Hien Vo', 'phone' => '+84 90 887 2214', 'roles' => ['cleaning'], 'area' => 'Thảo Điền', 'tasks_week' => 22, 'avg_time' => '28 min', 'avg_time_warn' => false, 'guest_info' => false, 'status' => 'active', 'avatar_color' => '#43503f'],
            ['team_type' => 'operations', 'name' => 'Tuan Le', 'phone' => '+84 93 442 1180', 'roles' => ['cash', 'courier'], 'area' => 'All buildings', 'tasks_week' => 12, 'avg_time' => '46 min', 'avg_time_warn' => true, 'guest_info' => true, 'status' => 'active', 'avatar_color' => '#8a6d3b'],
            ['team_type' => 'operations', 'name' => 'Nga Pham', 'phone' => '+84 90 221 6654', 'roles' => ['cleaning', 'courier'], 'area' => 'Bình Thạnh', 'tasks_week' => 15, 'avg_time' => '35 min', 'avg_time_warn' => false, 'guest_info' => false, 'status' => 'paused', 'avatar_color' => '#a35a4e'],
            ['team_type' => 'operations', 'name' => 'Bao Pham', 'phone' => '+84 90 441 9963', 'roles' => ['keys', 'courier'], 'area' => 'Bình Thạnh, Thủ Thiêm', 'tasks_week' => 9, 'avg_time' => '1 h 05 min', 'avg_time_warn' => false, 'guest_info' => false, 'status' => 'away', 'avatar_color' => '#2f6d7a'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function defaultInvitations(string $teamType): array
    {
        if ($teamType === 'sales') {
            return [
                ['team_type' => 'sales', 'name' => 'Hoang Vu', 'email' => 'hoang.vu@gmail.com', 'role' => 'Host Agent', 'area' => 'District 1', 'sent_days_ago' => 3, 'avatar_color' => '#7a8b6d'],
                ['team_type' => 'sales', 'name' => 'Sara Lindqvist', 'email' => 'sara.l@outlook.com', 'role' => 'Co-host · Indochine Living', 'area' => 'Thảo Điền', 'sent_days_ago' => 6, 'avatar_color' => '#5a7a8b'],
            ];
        }

        return [
            ['team_type' => 'operations', 'name' => 'Thuy Ngo', 'phone' => '+84 90 553 1187', 'email' => null, 'role' => 'Cleaning', 'area' => 'Thảo Điền', 'sent_days_ago' => 2, 'avatar_color' => '#1f7a44'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $members
     * @return array<string, mixed>
     */
    protected function buildStats(array $members, string $teamType): array
    {
        if ($teamType === 'sales') {
            $bookings = array_sum(array_column($members, 'bookings90'));
            $gross = array_sum(array_map(
                fn (array $member) => $member['type'] === 'agent' ? 0 : ($member['gross90'] ?? 0),
                $members,
            ));
            $owed = array_sum(array_map(
                fn (array $member) => $this->salesCommission($member),
                $members,
            ));

            return [
                'bookings90' => $bookings,
                'gross90' => $gross,
                'commissionOwed' => $owed,
            ];
        }

        $tasks = array_sum(array_column($members, 'tasksWeek'));
        $seeing = count(array_filter($members, fn (array $member) => $member['guestInfo']));

        return [
            'tasksWeek' => $tasks,
            'avgTime' => '41 min',
            'guestInfoSeeing' => $seeing,
            'guestInfoTotal' => count($members),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $members
     * @return array<int, string>
     */
    protected function areaOptions(array $members): array
    {
        $areas = array_values(array_unique(array_filter(array_map(
            fn (array $member) => $member['area'] ?? null,
            $members,
        ))));

        sort($areas);

        return array_values(array_filter($areas, fn (string $area) => $area !== 'All buildings'));
    }

    protected function presentMember(HostTeamMember $member): array
    {
        $color = $member->avatar_color ?: self::AVATAR_COLORS[abs(crc32($member->name)) % count(self::AVATAR_COLORS)];

        $apartmentIds = [];
        $bookings90 = 0;
        $gross90 = 0.0;

        if ($member->team_type === 'sales') {
            $apartmentIds = $this->memberApartmentIds($member);
            $bookings = $this->memberBookings($apartmentIds);
            $bookings90 = $bookings->count();
            $gross90 = (float) $bookings->where('status', '!=', 'cancelled')->sum('total');
        }

        $payload = [
            'id' => (string) $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'phone' => $member->phone,
            'org' => $member->org,
            'area' => $member->area,
            'func' => $member->func,
            'link' => $member->link,
            'type' => $member->member_type,
            'roles' => $member->roles ?? [],
            'apartments' => count($apartmentIds),
            'bookings90' => $bookings90,
            'gross90' => $gross90,
            'outPct' => (int) $member->out_pct,
            'pooled' => (bool) $member->pooled,
            'rating' => $member->rating !== null ? number_format((float) $member->rating, 1) : null,
            'tasksWeek' => (int) $member->tasks_week,
            'avgTime' => $member->avg_time,
            'avgTimeWarn' => (bool) $member->avg_time_warn,
            'guestInfo' => (bool) $member->guest_info,
            'status' => $member->status,
            'bg' => $color,
        ];

        return $payload;
    }

    protected function presentInvitation(HostTeamInvitation $invite): array
    {
        $color = self::AVATAR_COLORS[abs(crc32($invite->email ?: $invite->phone ?: $invite->name)) % count(self::AVATAR_COLORS)];
        $sentAt = $invite->sent_at ?? $invite->created_at;

        return [
            'id' => (string) $invite->id,
            'name' => $invite->name,
            'email' => $invite->email ?: $invite->phone,
            'role' => $invite->role,
            'area' => $invite->area,
            'sent' => $this->relativeSentLabel($sentAt),
            'sentAt' => $sentAt?->toIso8601String(),
            'bg' => $color,
        ];
    }

    /**
     * @param  array<string, mixed>  $member
     */
    protected function salesCommission(array $member): int
    {
        if (($member['pooled'] ?? false) || ($member['type'] ?? null) === 'agent') {
            return 0;
        }

        return (int) round((($member['gross90'] ?? 0) * ($member['outPct'] ?? 0)) / 100);
    }

    protected function relativeSentLabel(?Carbon $sentAt): string
    {
        if (! $sentAt) {
            return 'Just now';
        }

        $days = (int) $sentAt->diffInDays(now());

        if ($days <= 0) {
            return 'Just now';
        }

        if ($days === 1) {
            return '1 day ago';
        }

        return "{$days} days ago";
    }

    protected function nameFromContact(?string $email, ?string $phone): string
    {
        if ($email) {
            $local = explode('@', $email)[0];
            $parts = preg_split('/[._-]+/', $local) ?: [];

            return collect($parts)
                ->filter()
                ->map(fn (string $part) => ucfirst(strtolower($part)))
                ->join(' ');
        }

        return $phone ?: 'New member';
    }
}
