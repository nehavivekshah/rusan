<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendances;
use App\Models\Holidays;
use Carbon\Carbon;

class UserService
{
    /**
     * Generate attendance report data.
     *
     * @param User $user The authenticated user
     * @param bool $isAdmin Whether the user is an admin
     * @param int|null $selectedUserId Filter by specific user
     * @param string $range Date range filter
     * @return array
     */
    public function getAttendanceReport(User $user, $isAdmin, $selectedUserId, $range)
    {
        $dates = $this->getDateRange($range);

        if ($isAdmin) {
            $users = User::select('id', 'name', 'working_times')
                ->when($selectedUserId, fn($q) => $q->where('id', $selectedUserId))
                ->get();
        } else {
            $users = collect([$user]);
        }

        $userIds = $users->pluck('id')->toArray();

        // Load all attendance and holiday data at once
        $attendanceData = Attendances::whereIn('date', $dates)
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        $holidays = Holidays::whereIn('date', $dates)->get()->keyBy('date');

        return $this->processAttendanceData($users, $dates, $attendanceData, $holidays);
    }

    private function getDateRange($range)
    {
        $dates = [];
        $today = now()->toDateString();

        switch ($range) {
            case '7days':
                for ($i = 0; $i < 7; $i++) {
                    $dates[] = now()->subDays($i)->toDateString();
                }
                break;
            case 'month':
                $start = now()->startOfMonth();
                $end   = now();
                while ($start <= $end) {
                    $dates[] = $start->toDateString();
                    $start->addDay();
                }
                break;
            case 'last-month':
                $start = now()->subMonth()->startOfMonth();
                $end   = now()->subMonth()->endOfMonth();
                while ($start <= $end) {
                    $dates[] = $start->toDateString();
                    $start->addDay();
                }
                break;
            case 'year':
                $start = now()->startOfYear();
                $end   = now();
                while ($start <= $end) {
                    $dates[] = $start->toDateString();
                    $start->addDay();
                }
                break;
            case 'today':
            default:
                $dates[] = $today;
                break;
        }

        return $dates; 
    }

    private function processAttendanceData($users, $dates, $attendanceData, $holidays)
    {
        $final = [];
        $summary = [
            'working_days' => 0,
            'expected_hours' => 0,
            'worked_hours' => 0,
            'holidays' => 0,
            'leaves' => 0,
            'present' => 0,
            'absent' => 0,
        ];

        foreach ($users as $u) {
            $uid = $u->id;
            $userAttendance = $attendanceData->has($uid) ? $attendanceData[$uid]->keyBy('date') : collect();
            $workingTimes = json_decode($u->working_times, true);
            $start = $workingTimes['start'] ?? '10:00';
            $end = $workingTimes['end'] ?? '18:00';
            $expectedHours = Carbon::parse($end)->diffInHours(Carbon::parse($start));

            foreach ($dates as $date) {
                $dayName    = Carbon::parse($date)->format('l');
                $checkIn    = $checkOut = $method = $remarks = '-';
                $workedHours = 0;
                $type       = 'Working Day';
                $status     = 'Absent';
                $attId      = null;

                if ($userAttendance->has($date)) {
                    $att    = $userAttendance[$date];
                    $attId  = $att->id;
                    $checkIn  = $att->check_in  ?? '-';
                    $checkOut = $att->check_out ?? '-';
                    $method   = $att->method    ?? '-';
                    $remarks  = $att->remarks   ?? '-';
                    $status   = $att->status;

                    if ($checkIn !== '-' && $checkOut !== '-') {
                        $minutes     = Carbon::parse($checkOut)->diffInMinutes(Carbon::parse($checkIn));
                        $workedHours = round($minutes / 60, 2);
                    }
                } elseif (isset($holidays[$date])) {
                    $status = 'Holiday';
                    $type   = 'Holiday: ' . $holidays[$date]->title;
                } elseif ($dayName === 'Sunday') {
                    $status = 'Holiday';
                    $type   = 'Sunday';
                } else {
                    // No attendance record + not a holiday → mark Absent
                    $status = 'Absent';
                    $type   = 'Absent';
                }

                // Update summary counters
                if ($status === 'Present') $summary['present']++;
                if ($status === 'Leave') $summary['leaves']++;
                if ($status === 'Absent') $summary['absent']++;
                if ($status === 'Holiday') $summary['holidays']++;

                if (!in_array($status, ['Holiday'])) {
                    $summary['working_days']++;
                    $summary['expected_hours'] += $expectedHours;
                    $summary['worked_hours'] += $workedHours;
                }

                $final[] = [
                    'user'           => $u->name,
                    'user_id'        => $uid,
                    'date'           => $date,
                    'day'            => $dayName,
                    'status'         => $status,
                    'type'           => $type,
                    'check_in'       => $checkIn,
                    'check_out'      => $checkOut,
                    'method'         => $method,
                    'remarks'        => $remarks,
                    'expected_hours' => $expectedHours,
                    'worked_hours'   => $workedHours,
                    'att_id'         => $attId,
                ];
            }
        }

        return [
            'final' => $final,
            'summary' => $summary,
            'users' => $users
        ];
    }
    public function getUsersBySegment(User $authenticatedUser, $segment)
    {
        $query = User::leftjoin('companies', 'users.cid', '=', 'companies.id')
            ->leftjoin('roles', 'users.role', '=', 'roles.id')
            ->select('companies.name', 'roles.title', 'roles.subtitle', 'users.*');

        if (!$authenticatedUser->isMaster()) {
            if ($segment == 'admins') {
                $query->where('roles.features', '=', 'All');
            } elseif ($segment == 'employees') {
                $query->where('roles.features', '!=', 'All');
            }
        }

        return $query->get();
    }

    public function getRoles(User $authenticatedUser)
    {
        return \App\Models\Roles::get();
    }

    public function getUserDetails($uid, User $authenticatedUser)
    {
        $user = User::leftjoin('companies', 'users.cid', '=', 'companies.id')
            ->leftjoin('roles', 'users.role', '=', 'roles.id')
            ->select('companies.name as company', 'companies.img', 'roles.title', 'roles.features as roleFeatures', 'users.*')
            ->where('users.id', '=', $uid)
            ->first();

        $allUsers = User::leftjoin('companies', 'users.cid', '=', 'companies.id')
            ->leftjoin('roles', 'users.role', '=', 'roles.id')
            ->select('companies.name', 'roles.title', 'roles.subtitle', 'users.*')
            ->get();

        $roles = \App\Models\Roles::where('features', '!=', 'All')
            ->get();

        return [
            'users' => $user, // Keep naming consistent with view expected variable '$users'
            'allusers' => $allUsers,
            'roles' => $roles
        ];
    }
}
