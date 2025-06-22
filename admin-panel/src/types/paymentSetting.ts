export interface PaymentSetting {
  id: number;
  payment_type: 'bank_transfer' | 'e_wallet';
  name: string;
  account_number: string;
  account_name: string;
  description: string | null;
  is_active: boolean;
  logo_path: string | null;
  logo_url?: string;
  instructions: string[];
  created_at: string;
  updated_at: string;
}

export interface PaymentSettingFormData {
  payment_type: 'bank_transfer' | 'e_wallet';
  name: string;
  account_number: string;
  account_name: string;
  description: string | null;
  is_active: boolean;
  logo?: File;
  instructions: string[];
}
