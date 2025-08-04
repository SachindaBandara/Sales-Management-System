export interface User {
  id: number;
  name: string;
  email: string;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  products_count: number;
}

export interface Brand {
  id: number;
  name: string;
  slug: string;
  products_count: number;
}

export interface Product {
  id: number;
  name: string;
  description?: string;
  price: number;
  image_urls?: string[];
  first_image_url?: string;
  category?: Category;
  brand?: Brand;
  is_in_stock: boolean;
  created_at: string;
  updated_at: string;
}

export interface PaginatedProducts {
  data: Product[];
  current_page: number;
  last_page: number;
  prev_page_url: string | null;
  next_page_url: string | null;
  total: number;
  per_page: number;
  from: number;
  to: number;
}

export interface Filters {
  search?: string;
  category?: string;
  brand?: string;
  min_price?: number;
  max_price?: number;
  sort?: string;
}
