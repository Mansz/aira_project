export interface WhatsAppMessage {
  id: number;
  message_id?: string;
  phone_number: string;
  message: string;
  status: 'pending' | 'sent' | 'delivered' | 'read' | 'failed';
  direction: 'inbound' | 'outbound';
  user_id?: number;
  order_id?: number;
  metadata?: Record<string, any>;
  created_at: string;
  updated_at: string;
  user?: {
    id: number;
    name: string;
    email: string;
  };
  order?: {
    id: number;
    number: string;
  };
}

export interface WhatsAppAutoReply {
  id: number;
  keyword: string;
  response: string;
  is_regex: boolean;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface WhatsAppStats {
  total_messages: number;
  total_sent: number;
  total_received: number;
  pending_messages: number;
  failed_messages: number;
  active_auto_replies: number;
}
