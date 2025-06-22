export interface Admin {
  id: number;
  name: string;
  email: string;
  role: 'super_admin' | 'admin' | 'moderator';
  permissions: string[];
  is_active: boolean;
  avatar_url?: string;
  last_login_at?: string;
  created_at: string;
  updated_at: string;
}

export interface AdminActivity {
  id: number;
  admin_id: number;
  action: string;
  description: string;
  ip_address?: string;
  user_agent?: string;
  created_at: string;
  admin?: Admin;
}
