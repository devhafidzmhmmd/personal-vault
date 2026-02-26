<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $workspaceId = $request->session()->get('current_workspace_id');
        $shortcuts = collect();
        $todosToday = collect();
        $calendarDays = [];
        $calendarTitle = '';
        $calendarYear = null;
        $calendarMonth = null;
        $prevMonthUrl = null;
        $nextMonthUrl = null;

        $now = Carbon::now();
        $year = (int) $request->input('year', $now->year);
        $month = (int) $request->input('month', $now->month);
        $year = max(2020, min(2030, $year));
        $month = max(1, min(12, $month));

        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $prev = $monthStart->copy()->subMonth();
        $next = $monthStart->copy()->addMonth();
        $prevMonthUrl = route('dashboard', ['year' => $prev->year, 'month' => $prev->month]);
        $nextMonthUrl = route('dashboard', ['year' => $next->year, 'month' => $next->month]);

        if ($workspaceId) {
            $workspace = Workspace::where('user_id', $request->user()->id)->find($workspaceId);
            if ($workspace) {
                $shortcuts = $workspace->shortcuts;
                $todosToday = $workspace->todos()->whereDate('due_date', $now->toDateString())->orderBy('position')->orderBy('due_date')->get();
                $todosInMonth = $workspace->todos()
                    ->whereBetween('due_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->get();
                $datesWithTodos = $todosInMonth->pluck('due_date')
                    ->filter()
                    ->map(fn ($d) => $d->format('Y-m-d'))
                    ->unique()
                    ->values()
                    ->all();
                $datesWithCustomEvents = $workspace->customEvents()
                    ->whereBetween('event_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->get()
                    ->pluck('event_date')
                    ->map(fn ($d) => $d->format('Y-m-d'))
                    ->unique()
                    ->values()
                    ->all();
                $calendarTitle = $monthStart->translatedFormat('F Y');
                $calendarYear = $year;
                $calendarMonth = $month;
                $startWeekday = (int) $monthStart->dayOfWeek;
                $lastDay = (int) $monthEnd->day;
                $holidaysFixed = config('holidays.fixed', []);
                $holidaysByYear = config('holidays.by_year', []);
                $yearStr = $monthStart->format('Y');
                $calendarDays = [];
                for ($i = 0; $i < $startWeekday; $i++) {
                    $calendarDays[] = null;
                }
                for ($day = 1; $day <= $lastDay; $day++) {
                    $date = $monthStart->copy()->day($day);
                    $dateStr = $date->format('Y-m-d');
                    $md = $date->format('m-d');
                    $holidayName = $holidaysByYear[$yearStr][$dateStr] ?? $holidaysFixed[$md] ?? null;
                    $dow = (int) $date->dayOfWeek;
                    $calendarDays[] = [
                        'date' => $dateStr,
                        'day' => $day,
                        'is_today' => $dateStr === $now->toDateString(),
                        'has_todo' => in_array($dateStr, $datesWithTodos, true),
                        'has_custom_event' => in_array($dateStr, $datesWithCustomEvents, true),
                        'is_holiday' => $holidayName !== null,
                        'holiday_name' => $holidayName,
                        'is_weekend' => $dow === 0 || $dow === 6,
                    ];
                }
                $remainder = count($calendarDays) % 7;
                if ($remainder !== 0) {
                    $pad = 7 - $remainder;
                    for ($i = 0; $i < $pad; $i++) {
                        $calendarDays[] = null;
                    }
                }
            } else {
                $calendarTitle = $monthStart->translatedFormat('F Y');
                $calendarYear = $year;
                $calendarMonth = $month;
                $startWeekday = (int) $monthStart->dayOfWeek;
                $lastDay = (int) $monthEnd->day;
                $holidaysFixed = config('holidays.fixed', []);
                $holidaysByYear = config('holidays.by_year', []);
                $yearStr = $monthStart->format('Y');
                for ($i = 0; $i < $startWeekday; $i++) {
                    $calendarDays[] = null;
                }
                for ($day = 1; $day <= $lastDay; $day++) {
                    $date = $monthStart->copy()->day($day);
                    $dateStr = $date->format('Y-m-d');
                    $md = $date->format('m-d');
                    $holidayName = $holidaysByYear[$yearStr][$dateStr] ?? $holidaysFixed[$md] ?? null;
                    $dow = (int) $date->dayOfWeek;
                    $calendarDays[] = [
                        'date' => $dateStr,
                        'day' => $day,
                        'is_today' => $dateStr === $now->toDateString(),
                        'has_todo' => false,
                        'has_custom_event' => false,
                        'is_holiday' => $holidayName !== null,
                        'holiday_name' => $holidayName,
                        'is_weekend' => $dow === 0 || $dow === 6,
                    ];
                }
                $remainder = count($calendarDays) % 7;
                if ($remainder !== 0) {
                    for ($i = 0; $i < 7 - $remainder; $i++) {
                        $calendarDays[] = null;
                    }
                }
            }
        } else {
            $calendarTitle = $monthStart->translatedFormat('F Y');
            $calendarYear = $year;
            $calendarMonth = $month;
            $startWeekday = (int) $monthStart->dayOfWeek;
            $lastDay = (int) $monthEnd->day;
            $holidaysFixed = config('holidays.fixed', []);
            $holidaysByYear = config('holidays.by_year', []);
            $yearStr = $monthStart->format('Y');
            for ($i = 0; $i < $startWeekday; $i++) {
                $calendarDays[] = null;
            }
            for ($day = 1; $day <= $lastDay; $day++) {
                $date = $monthStart->copy()->day($day);
                $dateStr = $date->format('Y-m-d');
                $md = $date->format('m-d');
                $holidayName = $holidaysByYear[$yearStr][$dateStr] ?? $holidaysFixed[$md] ?? null;
                $dow = (int) $date->dayOfWeek;
                $calendarDays[] = [
                    'date' => $dateStr,
                    'day' => $day,
                    'is_today' => $dateStr === $now->toDateString(),
                    'has_todo' => false,
                    'has_custom_event' => false,
                    'is_holiday' => $holidayName !== null,
                    'holiday_name' => $holidayName,
                    'is_weekend' => $dow === 0 || $dow === 6,
                ];
            }
            $remainder = count($calendarDays) % 7;
            if ($remainder !== 0) {
                for ($i = 0; $i < 7 - $remainder; $i++) {
                    $calendarDays[] = null;
                }
            }
        }

        $dashboardParams = array_filter(['year' => $calendarYear, 'month' => $calendarMonth]);
        $eventsByDate = [];
        $monthDates = [];
        for ($d = $monthStart->copy(); $d->lte($monthEnd); $d->addDay()) {
            $monthDates[$d->format('Y-m-d')] = [];
        }
        $eventsByDate = $monthDates;

        $holidaysFixed = config('holidays.fixed', []);
        $holidaysByYear = config('holidays.by_year', []);
        $yearStr = $monthStart->format('Y');
        foreach (array_keys($eventsByDate) as $dateStr) {
            $md = substr($dateStr, 5, 5); // m-d
            $holidayName = $holidaysByYear[$yearStr][$dateStr] ?? $holidaysFixed[$md] ?? null;
            if ($holidayName !== null) {
                $eventsByDate[$dateStr][] = ['type' => 'holiday', 'title' => $holidayName, 'url' => null];
            }
        }

        if ($workspaceId) {
            $workspace = Workspace::where('user_id', $request->user()->id)->find($workspaceId);
            if ($workspace) {
                $todosInMonth = $workspace->todos()
                    ->with('shortcut')
                    ->whereBetween('due_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->get();
                foreach ($todosInMonth as $todo) {
                    $ds = $todo->due_date->format('Y-m-d');
                    if (isset($eventsByDate[$ds])) {
                        $event = [
                            'type' => 'todo',
                            'title' => $todo->title,
                            'url' => route('todos.edit', $todo),
                            'status' => $todo->status,
                        ];
                        if ($todo->relationLoaded('shortcut') && $todo->shortcut) {
                            $event['shortcut_title'] = $todo->shortcut->title;
                            $event['shortcut_url'] = $todo->shortcut->url;
                        }
                        $eventsByDate[$ds][] = $event;
                    }
                }
                $customInMonth = $workspace->customEvents()
                    ->whereBetween('event_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->get();
                $editQuery = empty($dashboardParams) ? '' : '?' . http_build_query($dashboardParams);
                foreach ($customInMonth as $ev) {
                    $ds = $ev->event_date->format('Y-m-d');
                    if (isset($eventsByDate[$ds])) {
                        $eventsByDate[$ds][] = [
                            'type' => 'custom',
                            'title' => $ev->title,
                            'url' => route('custom-events.edit', $ev) . $editQuery,
                            'id' => $ev->id,
                        ];
                    }
                }
            }
        }

        return view('dashboard.index', compact(
            'shortcuts',
            'todosToday',
            'calendarDays',
            'calendarTitle',
            'calendarYear',
            'calendarMonth',
            'prevMonthUrl',
            'nextMonthUrl',
            'eventsByDate',
            'dashboardParams'
        ));
    }
}
