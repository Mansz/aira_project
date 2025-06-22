export interface DashboardStats {
  users: number;
  totalOrders: number;
  totalProducts: number;
  totalRevenue: number;
  stats: {
    userGrowth: number;
    orderGrowth: number;
    productGrowth: number;
    revenueGrowth: number;
  };
  monthlyRevenue: Array<{
    month: string;
    revenue: number;
  }>;
  recentOrders: Array<{
    id: number;
    customer_name: string;
    total_amount: number;
    status: string;
    created_at: string;
  }>;
}

// Dashboard type definitions

export interface LiveStreamStats {
  isLive: boolean;
  viewers: number;
  duration: string;
  startTime?: string;
  title?: string;
}

// You can add other dashboard-related types here as needed
export interface DashboardMetrics {
  totalViews: number;
  totalFollowers: number;
  monthlyRevenue: number;
}

export interface StreamSession {
  id: string;
  title: string;
  startTime: string;
  endTime?: string;
  viewers: number;
  duration: string;
}

export interface RecentOrder {
  id: number;
  customer_name: string;
  total_amount: number;
  status: string;
  created_at: string;
}

export interface LiveStreamStats {
  isLive: boolean;
  viewers: number;
  duration: string;
  startTime?: string;
  title?: string;
}

export interface DashboardStats {
  recentOrders: RecentOrder[];
}
