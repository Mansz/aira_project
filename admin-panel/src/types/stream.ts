export interface LiveStream {
  id: string;
  title: string;
  description?: string;
  user_id: number;
  room_id: string;
  stream_id: string;
  status: 'scheduled' | 'live' | 'ended';
  viewer_count: number;
  stream_token?: string;
  pinned_product_id?: number;
  start_time?: string;
  end_time?: string;
  created_at: string;
  updated_at: string;
  
  // Relations
  user?: {
    id: number;
    name: string;
    email: string;
  };
  products?: LiveStreamProduct[];
  comments?: LiveComment[];
  orders?: LiveOrder[];
  analytics?: LiveAnalytics[];
  pinnedProduct?: {
    id: number;
    name: string;
    price: number;
    image_url?: string;
  };
}

export interface LiveStreamProduct {
  id: number;
  live_stream_id: string;
  product_id: number;
  created_at: string;
  updated_at: string;
  
  // Relations
  product: {
    id: number;
    name: string;
    price: number;
    image_url?: string;
    stock: number;
  };
}

export interface LiveComment {
  id: number;
  live_stream_id: string;
  user_id: number;
  content: string;
  type: 'CHAT' | 'ORDER';
  is_flagged: boolean;
  metadata?: any;
  created_at: string;
  updated_at: string;
  
  // Relations
  user: {
    id: number;
    name: string;
    avatar?: string;
  };
  stream: LiveStream;
}

export interface LiveOrder {
  id: string;
  live_stream_id: string;
  user_id: number;
  customer: {
    name: string;
    phone: string;
    email?: string;
    address?: string;
  };
  products: LiveOrderProduct[];
  total: number;
  status: 'pending' | 'confirmed' | 'cancelled';
  paymentStatus: 'unpaid' | 'paid' | 'failed';
  orderTime: string;
  sessionId: string;
  notes?: string;
  created_at: string;
  updated_at: string;
  
  // Relations
  user: {
    id: number;
    name: string;
  };
  stream: LiveStream;
}

export interface LiveOrderProduct {
  id: number;
  live_order_id: string;
  product_id: number;
  name: string;
  price: number;
  quantity: number;
  subtotal: number;
  created_at: string;
  updated_at: string;
  
  // Relations
  product: {
    id: number;
    name: string;
    price: number;
    image_url?: string;
  };
}

export interface LiveVoucher {
  id: number;
  live_stream_id: string;
  code: string;
  name: string;
  type: 'percentage' | 'fixed';
  value: number;
  min_purchase?: number;
  max_discount?: number;
  usage_limit?: number;
  used_count: number;
  is_active: boolean;
  expires_at?: string;
  created_at: string;
  updated_at: string;
  
  // Relations
  stream: LiveStream;
}

export interface LiveAnalytics {
  id: number;
  live_stream_id: string;
  total_comments: number;
  active_users: number;
  peak_viewers?: number;
  engagement_rate?: number;
  orders_count?: number;
  revenue?: number;
  recorded_at: string;
  created_at: string;
  updated_at: string;
  
  // Relations
  stream: LiveStream;
}

export interface StreamStats {
  totalStreams: number;
  activeStreams: number;
  totalViewers: number;
  totalComments: number;
  totalOrders: number;
  totalRevenue: number;
  averageViewTime?: number;
  popularTimes?: {
    hour: number;
    viewers: number;
  }[];
}

export interface LiveStreamCreateRequest {
  title: string;
  description?: string;
  stream_id?: string;
}

export interface LiveStreamTokenRequest {
  stream_title: string;
}

export interface LiveStreamTokenResponse {
  token: string;
  room_id: string;
  stream_id: string;
  expires_at: string;
}

export interface PinProductRequest {
  live_stream_id: string;
  product_id: number;
}

export interface StreamAnalyticsRequest {
  live_stream_id: string;
  total_comments: number;
  active_users: number;
}

// WebSocket Events
export interface NewOrderEvent {
  order: LiveOrder;
}

export interface OrderUpdatedEvent {
  order: LiveOrder;
}

export interface OrderConfirmedEvent {
  order: LiveOrder;
}

export interface NewCommentEvent {
  comment: LiveComment;
}

export interface CommentDeletedEvent {
  commentId: number;
}

export interface ViewerCountUpdatedEvent {
  streamId: string;
  viewerCount: number;
}

export interface ProductPinnedEvent {
  streamId: string;
  product: {
    id: number;
    name: string;
    price: number;
    image_url?: string;
  };
}
