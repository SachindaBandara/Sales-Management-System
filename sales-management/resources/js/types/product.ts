export interface Brand {
  id: number;
  name: string;
}

export interface Category {
  id: number;
  name: string;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  short_description: string | null;
  sku: string;
  price: number;
  compare_price: number | null;
  stock_quantity: number;
  track_quantity: boolean;
  images: string[];
  image_urls: string[];
  first_image_url: string | null; // Aligned with Index.vue's permissive type
  image_count: number;
  status: string;
  brand: Brand | null;
  category: Category | null;
  created_at: string;
  discount_percentage: number;
  is_in_stock: boolean;
}

export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

export interface Pagination {
  data: Product[];
  from: number;
  to: number;
  total: number;
  links: PaginationLink[];
}

export interface Filters {
  search?: string;
  status?: string;
  brand_id?: string;
  category_id?: string;
}

export interface ImportError {
  row: number;
  errors: string[];
}

export interface ImportResults {
  success_count: number;
  update_count: number;
  error_count: number;
  total_processed: number;
}

