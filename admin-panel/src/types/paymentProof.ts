export interface PaymentProof {
  id: number;
  order_id: number;
  file_path: string;
  status: 'pending' | 'verified' | 'rejected';
  verified_at: string | null;
  verified_by: number | null;
  notes: string | null;
  created_at: string;
  updated_at: string;
  order?: {
    id: number;
    user?: {
      name: string;
    };
  };
  verifiedBy?: {
    id: number;
    name: string;
  };
}

export interface PaymentProofFormData {
  notes?: string;
}
