import { create } from 'zustand';
import axios from '@/lib/axios';

// Definisi ApiError class jika belum ada
class ApiError extends Error {
  constructor(message: string, public status?: number) {
    super(message);
    this.name = 'ApiError';
  }
}

// Base URL untuk API - Fixed to handle undefined process.env
const getApiBaseUrl = () => {
  // Option 1: Use window object for runtime environment variables
  if (typeof window !== 'undefined' && (window as any).ENV?.REACT_APP_API_URL) {
    return (window as any).ENV.REACT_APP_API_URL;
  }
  
  // Option 2: Check if process is defined (build time)
  if (typeof process !== 'undefined' && process.env?.REACT_APP_API_URL) {
    return process.env.REACT_APP_API_URL;
  }
  
  // Option 3: Default fallback
  return 'http://localhost:8000/api';
};

const API_BASE_URL = getApiBaseUrl();

// Helper function untuk API calls
const apiCall = async (endpoint: string, options: RequestInit = {}) => {
  const url = `${API_BASE_URL}${endpoint}`;
  
  // Get token from localStorage safely
  const getToken = () => {
    if (typeof window !== 'undefined' && window.localStorage) {
      return localStorage.getItem('token');
    }
    return null;
  };

  const token = getToken();
  const defaultHeaders = {
    'Content-Type': 'application/json',
    // Tambahkan authorization header jika ada token
    ...(token && {
      'Authorization': `Bearer ${token}`
    })
  };

  const config: RequestInit = {
    ...options,
    headers: {
      ...defaultHeaders,
      ...options.headers,
    },
  };

  try {
    const response = await fetch(url, config);
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new ApiError(
        errorData.message || `HTTP Error ${response.status}`,
        response.status
      );
    }

    return await response.json();
  } catch (error) {
    if (error instanceof ApiError) {
      throw error;
    }
    throw new ApiError(error instanceof Error ? error.message : 'Network error');
  }
};

// API functions dengan implementasi yang lebih realistis
const api = {
  // Dashboard
  getDashboardStats: async () => {
    try {
      return await apiCall('/dashboard/stats');
    } catch (error) {
      // Fallback data jika API belum tersedia
      console.warn('Using fallback dashboard data:', error);
      return {
        totalCustomers: 150,
        customerGrowth: 12,
        totalOrders: 89,
        orderGrowth: 8,
        totalProducts: 45,
        productGrowth: 5,
        totalRevenue: 25000000,
        revenueGrowth: 15,
        revenueChart: [
          { month: 'Jan', revenue: 10000000 },
          { month: 'Feb', revenue: 15000000 },
          { month: 'Mar', revenue: 25000000 }
        ],
        recentOrders: [
          { 
            id: 1, 
            customer_name: 'John Doe', 
            total_amount: 150000, 
            status: 'completed', 
            created_at: '2024-01-15' 
          }
        ]
      };
    }
  },

  // Products
  getProducts: async (params?: Record<string, any>) => {
    const queryParams = params ? new URLSearchParams(params).toString() : '';
    const endpoint = `/products${queryParams ? `?${queryParams}` : ''}`;
    
    try {
      return await apiCall(endpoint);
    } catch (error) {
      console.warn('Using fallback products data:', error);
      return { data: [], total: 0 };
    }
  },

  createProduct: async (data: FormData) => {
    return await apiCall('/products', {
      method: 'POST',
      body: data,
      headers: {} // FormData akan set content-type sendiri
    });
  },

  updateProduct: async (id: number, data: FormData) => {
    return await apiCall(`/products/${id}`, {
      method: 'PUT',
      body: data,
      headers: {} // FormData akan set content-type sendiri
    });
  },

  deleteProduct: async (id: number) => {
    return await apiCall(`/products/${id}`, {
      method: 'DELETE'
    });
  },

  // Orders
  getOrders: async (params?: Record<string, any>) => {
    const queryParams = params ? new URLSearchParams(params).toString() : '';
    const endpoint = `/orders${queryParams ? `?${queryParams}` : ''}`;
    
    try {
      return await apiCall(endpoint);
    } catch (error) {
      console.warn('Using fallback orders data:', error);
      return { data: [], total: 0 };
    }
  },

  updateOrderStatus: async (id: number, status: string) => {
    return await apiCall(`/orders/${id}/status`, {
      method: 'PATCH',
      body: JSON.stringify({ status })
    });
  },

  // Live Stream
  getLiveStreamStats: async () => {
    try {
      return await apiCall('/v1/admin/streaming/stats');
    } catch (error) {
      console.warn('Using fallback live stream data:', error);
      return {
        isLive: false,
        viewers: 0,
        duration: '00:00',
        sessions: []
      };
    }
  },

  startLiveStream: async (data: Record<string, any>) => {
    return await apiCall('/v1/admin/streaming/start', {
      method: 'POST',
      body: JSON.stringify(data)
    });
  },

  endLiveStream: async () => {
    return await apiCall('/v1/admin/streaming/end', {
      method: 'POST'
    });
  },

  // WhatsApp
  getWhatsAppStats: async () => {
    try {
      return await apiCall('/whatsapp/stats');
    } catch (error) {
      console.warn('Using fallback WhatsApp data:', error);
      return {
        total_messages: 0,
        total_contacts: 0,
        messages_24h: 0,
        active_sessions: 0
      };
    }
  },

  sendWhatsAppMessage: async (data: Record<string, any>) => {
    return await apiCall('/whatsapp/send', {
      method: 'POST',
      body: JSON.stringify(data)
    });
  },

  // Settings
  getPaymentSettings: async () => {
    try {
      return await apiCall('/settings/payment');
    } catch (error) {
      console.warn('Using fallback payment settings:', error);
      return {
        id: 1,
        provider: 'midtrans',
        is_active: true,
        config: {},
        created_at: '2024-01-01',
        updated_at: '2024-01-01'
      };
    }
  },

  updatePaymentSettings: async (data: Record<string, any>) => {
    return await apiCall('/settings/payment', {
      method: 'PUT',
      body: JSON.stringify(data)
    });
  }
};

// Types for Chart Data
interface RevenueChartData {
  month: string;
  revenue: number;
}

// Dashboard Stats Interface - Updated to match component usage
interface DashboardStats {
  totalCustomers: number;
  customerGrowth: number;
  totalOrders: number;
  orderGrowth: number;
  totalProducts: number;
  productGrowth: number;
  totalRevenue: number;
  revenueGrowth: number;
  revenueChart: RevenueChartData[];
  recentOrders: OrderSummary[];
  topProducts?: ProductSummary[];
}

interface ProductSummary {
  id: number;
  name: string;
  price: number;
  stock: number;
  total_sold: number;
}

// Updated OrderSummary to match Dashboard component usage
interface OrderSummary {
  id: number | string;
  customer: string; // Changed from customer_name to customer
  total: number; // Changed from total_amount to total
  status: string;
  date: string; // Changed from created_at to date
}

interface Product {
  id: number;
  name: string;
  description: string;
  price: number;
  stock: number;
  category_id: number;
  category_name: string;
  images: string[];
  created_at: string;
  updated_at: string;
}

interface Order {
  id: number;
  user_id: number;
  customer_name: string;
  status: string;
  total_amount: number;
  items: OrderItem[];
  shipping_address: ShippingAddress;
  payment_status: string;
  created_at: string;
  updated_at: string;
}

interface OrderItem {
  id: number;
  product_id: number;
  product_name: string;
  quantity: number;
  price: number;
  subtotal: number;
}

interface ShippingAddress {
  id: number;
  recipient_name: string;
  phone: string;
  address: string;
  city: string;
  postal_code: string;
}

// Session interface for Live Stream
interface Session {
  id: string | number;
  title: string;
  viewers: number;
  duration: string;
  status: string;
}

// Updated LiveStreamStats to match Dashboard component usage
interface LiveStreamStats {
  isLive: boolean;
  viewers?: number;
  duration?: string;
  sessions?: Session[];
  peak_viewers?: number;
  total_orders?: number;
  total_revenue?: number;
  start_time?: string;
}

interface WhatsAppStats {
  total_messages: number;
  total_contacts: number;
  messages_24h: 0;
  active_sessions: number;
}

interface PaymentSettings {
  id: number;
  provider: string;
  is_active: boolean;
  config: Record<string, any>;
  created_at: string;
  updated_at: string;
}

// API Response types
interface PaginatedResponse<T> {
  data: T[];
  total: number;
  page?: number;
  limit?: number;
}

interface StoreState {
  // Dashboard
  dashboardStats: DashboardStats | null;
  loadingDashboard: boolean;
  getDashboardStats: () => Promise<void>;

  // Products
  products: Product[];
  totalProducts: number;
  loadingProducts: boolean;
  getProducts: (params?: Record<string, any>) => Promise<void>;
  createProduct: (data: FormData) => Promise<void>;
  updateProduct: (id: number, data: FormData) => Promise<void>;
  deleteProduct: (id: number) => Promise<void>;

  // Orders
  orders: Order[];
  totalOrders: number;
  loadingOrders: boolean;
  getOrders: (params?: Record<string, any>) => Promise<void>;
  updateOrderStatus: (id: number, status: string) => Promise<void>;

  // Live Streaming
  liveStreamStats: LiveStreamStats | null;
  loadingLiveStream: boolean;
  getLiveStreamStats: () => Promise<void>;
  startLiveStream: (data: Record<string, any>) => Promise<void>;
  endLiveStream: () => Promise<void>;

  // WhatsApp
  whatsappStats: WhatsAppStats | null;
  loadingWhatsApp: boolean;
  getWhatsAppStats: () => Promise<void>;
  sendWhatsAppMessage: (data: Record<string, any>) => Promise<void>;

  // Settings
  paymentSettings: PaymentSettings | null;
  loadingSettings: boolean;
  getPaymentSettings: () => Promise<void>;
  updatePaymentSettings: (data: Record<string, any>) => Promise<void>;

  // Error handling
  error: string | null;
  setError: (error: string | null) => void;
  clearError: () => void;
}

export const useStore = create<StoreState>((set, get) => ({
  // Dashboard
  dashboardStats: null,
  loadingDashboard: false,
  getDashboardStats: async () => {
    try {
      set({ loadingDashboard: true, error: null });
      const response = await api.getDashboardStats();
      
      // Transform data if needed to match interface
      const transformedData: DashboardStats = {
        totalCustomers: response.totalCustomers || 0,
        customerGrowth: response.customerGrowth || 0,
        totalOrders: response.totalOrders || 0,
        orderGrowth: response.orderGrowth || 0,
        totalProducts: response.totalProducts || 0,
        productGrowth: response.productGrowth || 0,
        totalRevenue: response.totalRevenue || 0,
        revenueGrowth: response.revenueGrowth || 0,
        revenueChart: response.revenueChart || [],
        recentOrders: response.recentOrders?.map((order: any) => ({
          id: order.id,
          customer: order.customer_name || order.customer || '',
          total: order.total_amount || order.total || 0,
          status: order.status || '',
          date: order.created_at ? new Date(order.created_at).toLocaleDateString() : order.date || ''
        })) || [],
        topProducts: response.topProducts || []
      };

      set({ dashboardStats: transformedData });
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to load dashboard stats';
      console.error('Dashboard stats error:', err);
      set({ error: errorMessage });
    } finally {
      set({ loadingDashboard: false });
    }
  },

  // Products
  products: [],
  totalProducts: 0,
  loadingProducts: false,
  getProducts: async (params) => {
    try {
      set({ loadingProducts: true, error: null });
      const response = await api.getProducts(params);
      
      // Handle both direct array and paginated response
      if (Array.isArray(response)) {
        set({ products: response, totalProducts: response.length });
      } else {
        set({ 
          products: response.data || [], 
          totalProducts: response.total || 0 
        });
      }
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to load products';
      console.error('Products error:', err);
      set({ error: errorMessage });
    } finally {
      set({ loadingProducts: false });
    }
  },
  createProduct: async (data) => {
    try {
      set({ error: null });
      await api.createProduct(data);
      await get().getProducts();
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to create product';
      set({ error: errorMessage });
      throw err;
    }
  },
  updateProduct: async (id, data) => {
    try {
      set({ error: null });
      await api.updateProduct(id, data);
      await get().getProducts();
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to update product';
      set({ error: errorMessage });
      throw err;
    }
  },
  deleteProduct: async (id) => {
    try {
      set({ error: null });
      await api.deleteProduct(id);
      await get().getProducts();
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to delete product';
      set({ error: errorMessage });
      throw err;
    }
  },

  // Orders
  orders: [],
  totalOrders: 0,
  loadingOrders: false,
  getOrders: async (params) => {
    try {
      set({ loadingOrders: true, error: null });
      const response = await api.getOrders(params);
      
      // Handle both direct array and paginated response
      if (Array.isArray(response)) {
        set({ orders: response, totalOrders: response.length });
      } else {
        set({ 
          orders: response.data || [], 
          totalOrders: response.total || 0 
        });
      }
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to load orders';
      console.error('Orders error:', err);
      set({ error: errorMessage });
    } finally {
      set({ loadingOrders: false });
    }
  },
  updateOrderStatus: async (id, status) => {
    try {
      set({ error: null });
      await api.updateOrderStatus(id, status);
      await get().getOrders();
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to update order status';
      set({ error: errorMessage });
      throw err;
    }
  },

  // Live Streaming
  liveStreamStats: null,
  loadingLiveStream: false,
  getLiveStreamStats: async () => {
    try {
      set({ loadingLiveStream: true, error: null });
      const response = await api.getLiveStreamStats();
      
      // Transform data to match interface
      const transformedData: LiveStreamStats = {
        isLive: response.isLive || false,
        viewers: response.viewers || 0,
        duration: response.duration || '00:00',
        sessions: response.sessions?.map((session: any) => ({
          id: session.id,
          title: session.title || 'Live Session',
          viewers: session.viewers || 0,
          duration: session.duration || '00:00',
          status: session.status || 'completed'
        })) || [],
        peak_viewers: response.peak_viewers,
        total_orders: response.total_orders,
        total_revenue: response.total_revenue,
        start_time: response.start_time
      };

      set({ liveStreamStats: transformedData });
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to load live stream stats';
      console.error('Live stream stats error:', err);
      set({ error: errorMessage });
    } finally {
      set({ loadingLiveStream: false });
    }
  },
  startLiveStream: async (data) => {
    try {
      set({ error: null });
      await api.startLiveStream(data);
      await get().getLiveStreamStats();
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to start live stream';
      set({ error: errorMessage });
      throw err;
    }
  },
  endLiveStream: async () => {
    try {
      set({ error: null });
      await api.endLiveStream();
      await get().getLiveStreamStats();
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to end live stream';
      set({ error: errorMessage });
      throw err;
    }
  },

  // WhatsApp
  whatsappStats: null,
  loadingWhatsApp: false,
  getWhatsAppStats: async () => {
    try {
      set({ loadingWhatsApp: true, error: null });
      const data = await api.getWhatsAppStats();
      set({ whatsappStats: data });
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to load WhatsApp stats';
      console.error('WhatsApp stats error:', err);
      set({ error: errorMessage });
    } finally {
      set({ loadingWhatsApp: false });
    }
  },
  sendWhatsAppMessage: async (data) => {
    try {
      set({ error: null });
      await api.sendWhatsAppMessage(data);
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to send WhatsApp message';
      set({ error: errorMessage });
      throw err;
    }
  },

  // Settings
  paymentSettings: null,
  loadingSettings: false,
  getPaymentSettings: async () => {
    try {
      set({ loadingSettings: true, error: null });
      const data = await api.getPaymentSettings();
      set({ paymentSettings: data });
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to load payment settings';
      console.error('Payment settings error:', err);
      set({ error: errorMessage });
    } finally {
      set({ loadingSettings: false });
    }
  },
  updatePaymentSettings: async (data) => {
    try {
      set({ error: null });
      await api.updatePaymentSettings(data);
      await get().getPaymentSettings();
    } catch (err) {
      const errorMessage = err instanceof ApiError ? err.message : 'Failed to update payment settings';
      set({ error: errorMessage });
      throw err;
    }
  },

  // Error handling
  error: null,
  setError: (error) => set({ error }),
  clearError: () => set({ error: null }),
}));

// Export types untuk digunakan di komponen lain
export type { 
  DashboardStats, 
  LiveStreamStats, 
  Product, 
  Order, 
  OrderSummary,
  ProductSummary,
  WhatsAppStats, 
  PaymentSettings 
};