export interface OrderItem {
  id: number;
  product_id: number;
  order_id: number;
  quantity: number;
  price: number;
  notes?: string;
  product?: {
    name: string;
    image?: string;
  };
}

export interface Order {
  id: number;
  user_id: number;
  total_price: number;
  total_amount: number;
  shipping_address: string;
  payment_method: string;
  status: 'Menunggu Pembayaran' | 'Menunggu Konfirmasi' | 'Diproses' | 'Dikirim' | 'Selesai' | 'Dibatalkan';
  shipping_status: string;
  tracking_number?: string;
  shipping_courier?: string;
  shipping_proof_path?: string;
  fcm_token?: string;
  created_at: string;
  updated_at: string;
  user?: {
    name: string;
    email: string;
    phone?: string;
  };
  items?: OrderItem[];
  payment_proof?: {
    image_url: string;
    verified_at: string | null;
  };
}

export interface OrderStats {
  total_orders: number;
  pending_orders: number;
  processing_orders: number;
  shipped_orders: number;
  completed_orders: number;
  cancelled_orders: number;
  total_revenue: number;
  today_orders: number;
  this_month_orders: number;
}
