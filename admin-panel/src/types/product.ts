export interface Product {
  id: number;
  name: string;
  slug?: string;
  description: string;
  category: string;
  category_id: number;
  price: number;
  stock: number;
  weight?: number;
  color?: string;
  size?: string;
  sku?: string;
  image: string | null;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface ProductCategory {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  icon: string | null;
  is_active: boolean;
  products_count: number;
  created_at: string;
  updated_at: string;
}
