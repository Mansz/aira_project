export interface Payment {
  id: number;
  orderId: string;
  customer: {
    name: string;
    email: string;
  };
  amount: number;
  method: 'bank_transfer' | 'credit_card' | string;
  status: 'completed' | 'pending' | 'failed' | string;
  date: string;
  bankAccount?: {
    bank: string;
    number: string;
    holder: string;
  };
  cardInfo?: {
    type: string;
    last4: string;
  };
  created_at: string;
  updated_at: string;
}
