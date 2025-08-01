<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => $this->getUserStatistics(),
            'orderStats' => $this->getOrderStatistics(),
            'dailyRevenueData' => $this->getDailyRevenueData(),
            'dailyOrdersData' => $this->getDailyOrdersData(),
            'monthlyRevenueData' => $this->getMonthlyRevenueData(),
            'monthlyOrdersData' => $this->getMonthlyOrdersData(),
        ]);
    }

    /**
     * Get user statistics.
     */
    private function getUserStatistics()
    {
        $recentRegistrations = User::where('role', 'customer')
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'email', 'created_at']);

        return [
            'total_customers' => User::where('role', 'customer')->count(),
            'active_customers' => User::where('role', 'customer')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'recent_registrations' => $recentRegistrations,
        ];
    }

    /**
     * Get order statistics.
     */
    private function getOrderStatistics()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        return [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'todays_orders' => Order::whereDate('created_at', $today)->count(),
            'this_month_orders' => Order::whereDate('created_at', '>=', $thisMonth)->count(),
            'total_revenue' => Order::whereNotIn('status', ['cancelled'])->sum('total_amount'),
            'todays_revenue' => Order::whereDate('created_at', $today)
                ->whereNotIn('status', ['cancelled'])
                ->sum('total_amount'),
            'this_month_revenue' => Order::whereDate('created_at', '>=', $thisMonth)
                ->whereNotIn('status', ['cancelled'])
                ->sum('total_amount'),
        ];
    }

    /**
     * Get revenue data based on period.
     */
    private function getRevenueData($period = '7days')
    {
        $data = [];
        $labels = [];

        switch ($period) {
            case '7days':
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i)->startOfDay();
                    $endDate = now()->subDays($i)->endOfDay();

                    $revenue = Order::whereBetween('created_at', [$date, $endDate])
                        ->whereNotIn('status', ['cancelled'])
                        ->sum('total_amount');

                    $data[] = (float) $revenue;
                    $labels[] = $date->format('M d');
                }
                break;

            case '1month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i)->startOfDay();
                    $endDate = now()->subDays($i)->endOfDay();

                    $revenue = Order::whereBetween('created_at', [$date, $endDate])
                        ->whereNotIn('status', ['cancelled'])
                        ->sum('total_amount');

                    $data[] = (float) $revenue;
                    $labels[] = $date->format('M d');
                }
                break;

            case '6months':
                for ($i = 5; $i >= 0; $i--) {
                    $startDate = now()->subMonths($i)->startOfMonth();
                    $endDate = now()->subMonths($i)->endOfMonth();

                    $revenue = Order::whereBetween('created_at', [$startDate, $endDate])
                        ->whereNotIn('status', ['cancelled'])
                        ->sum('total_amount');

                    $data[] = (float) $revenue;
                    $labels[] = $startDate->format('M Y');
                }
                break;

            case '1year':
                for ($i = 11; $i >= 0; $i--) {
                    $startDate = now()->subMonths($i)->startOfMonth();
                    $endDate = now()->subMonths($i)->endOfMonth();

                    $revenue = Order::whereBetween('created_at', [$startDate, $endDate])
                        ->whereNotIn('status', ['cancelled'])
                        ->sum('total_amount');

                    $data[] = (float) $revenue;
                    $labels[] = $startDate->format('M Y');
                }
                break;

            case 'lifetime':
                $firstOrder = Order::oldest()->first();
                if (!$firstOrder) {
                    break;
                }

                $start = Carbon::parse($firstOrder->created_at)->startOfMonth();
                $end = now()->endOfMonth();

                while ($start <= $end) {
                    $startDate = $start->copy()->startOfMonth();
                    $endDate = $start->copy()->endOfMonth();

                    $revenue = Order::whereBetween('created_at', [$startDate, $endDate])
                        ->whereNotIn('status', ['cancelled'])
                        ->sum('total_amount');

                    $data[] = (float) $revenue;
                    $labels[] = $startDate->format('M Y');

                    $start->addMonth();
                }
                break;
        }

        return compact('data', 'labels');
    }

    /**
     * Get orders data based on period.
     */
    private function getOrdersData($period = '7days')
    {
        $data = [];
        $labels = [];

        switch ($period) {
            case '7days':
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i)->startOfDay();
                    $endDate = now()->subDays($i)->endOfDay();

                    $orders = Order::whereBetween('created_at', [$date, $endDate])->count();

                    $data[] = $orders;
                    $labels[] = $date->format('M d');
                }
                break;

            case '1month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i)->startOfDay();
                    $endDate = now()->subDays($i)->endOfDay();

                    $orders = Order::whereBetween('created_at', [$date, $endDate])->count();

                    $data[] = $orders;
                    $labels[] = $date->format('M d');
                }
                break;

            case '6months':
                for ($i = 5; $i >= 0; $i--) {
                    $startDate = now()->subMonths($i)->startOfMonth();
                    $endDate = now()->subMonths($i)->endOfMonth();

                    $orders = Order::whereBetween('created_at', [$startDate, $endDate])->count();

                    $data[] = $orders;
                    $labels[] = $startDate->format('M Y');
                }
                break;

            case '1year':
                for ($i = 11; $i >= 0; $i--) {
                    $startDate = now()->subMonths($i)->startOfMonth();
                    $endDate = now()->subMonths($i)->endOfMonth();

                    $orders = Order::whereBetween('created_at', [$startDate, $endDate])->count();

                    $data[] = $orders;
                    $labels[] = $startDate->format('M Y');
                }
                break;

            case 'lifetime':
                $firstOrder = Order::oldest()->first();
                if (!$firstOrder) {
                    break;
                }

                $start = Carbon::parse($firstOrder->created_at)->startOfMonth();
                $end = now()->endOfMonth();

                while ($start <= $end) {
                    $startDate = $start->copy()->startOfMonth();
                    $endDate = $start->copy()->endOfMonth();

                    $orders = Order::whereBetween('created_at', [$startDate, $endDate])->count();

                    $data[] = $orders;
                    $labels[] = $startDate->format('M Y');

                    $start->addMonth();
                }
                break;
        }

        return compact('data', 'labels');
    }

    /**
     * Legacy methods for backward compatibility
     */
    private function getDailyRevenueData()
    {
        return $this->getRevenueData('7days')['data'];
    }

    private function getDailyOrdersData()
    {
        return $this->getOrdersData('7days')['data'];
    }

    private function getMonthlyRevenueData()
    {
        return $this->getRevenueData('6months')['data'];
    }

    private function getMonthlyOrdersData()
    {
        return $this->getOrdersData('6months')['data'];
    }

    /**
     * Get chart data via AJAX for real-time updates.
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'revenue');
        $period = $request->get('period', '7days');

        if ($type === 'revenue') {
            return response()->json($this->getRevenueData($period));
        } elseif ($type === 'orders') {
            return response()->json($this->getOrdersData($period));
        }

        return response()->json([
            'revenue' => $this->getRevenueData($period),
            'orders' => $this->getOrdersData($period),
        ]);
    }
}
