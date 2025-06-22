export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  whatsapp: string;
  google_id?: string;
  avatar?: string;
  is_active: boolean;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface UserStats {
  total: number;
  active: number;
  inactive: number;
  recent: User[];
}

export interface UserFilters {
  search?: string;
  status?: 'active' | 'inactive';
  sort_field?: string;
  sort_order?: 'asc' | 'desc';
  page?: number;
  per_page?: number;
}

export interface UserFormData {
  name: string;
  email: string;
  phone?: string;
  whatsapp: string;
  password?: string;
  is_active: boolean;
  avatar?: string;
}
