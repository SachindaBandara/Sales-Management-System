export interface User {
    id: number;
    name: string;
    first_name?: string;
    last_name?: string;
    email?: string;
    phone?: string;
}

export interface OrderItem {
id: number;
  product: Product;
  price: number;
  total: number;
  product_id: number;
  product_name: string;
  product_sku: string;
  product_price: number;
  quantity: number;
  total_price: number;
  product_snapshot: {
    name: string;
    sku: string;
    price: number;
    image: string;
    description: string;
  };
}

export interface Address {
  name?: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  address_line_1: string;
  address_line_2?: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
}

export interface Order {
  id: number;
  order_number: string;
  user: User
  user_id: number;
  status: string;
  subtotal: number;
  tax_amount: number;
  shipping_amount: number;
  discount_amount: number;
  total_amount: number;
  payment_method: string;
  payment_status: string;
  billing_address: Address;
  shipping_address: Address;
  notes?: string;
  created_at: string;
  items: OrderItem[];
  items_count: number;
  processed_at?: string;
  shipped_at?: string;
  delivered_at?: string;
}

export interface Product {
  id: number;
  name: string;
  sku: string;
  image?: string;
}

export interface PaginatedOrders {
  data: Order[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface CartItem {
    name: string;
    sku?: string;
    price: number;
    quantity: number;
    image?: string;
}

export interface CartTotals {
    subtotal: number;
    tax: number;
    taxRate: number;
    total: number;
}

export interface FormData {
    billing_address: Address;
    shipping_address: Address;
    shipping_same_as_billing: boolean;
    payment_method: 'cash_on_delivery' | 'card' | 'paypal';
    notes: string;
}

export interface Errors {
    [key: string]: string;
}

export interface Address {
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  address_line_1: string;
  address_line_2?: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
}

export interface OrdersPagination {
  data: Order[]
  current_page: number
  from: number
  to: number
  total: number
  prev_page_url: string | null
  next_page_url: string | null
}

export interface Statistics {
  total_orders: number
  pending_orders: number
  processing_orders: number
  shipped_orders: number
  delivered_orders: number
  cancelled_orders: number
  todays_orders: number
  this_month_orders: number
  total_revenue: number
  todays_revenue: number
  this_month_revenue: number
}

export interface Filters {
  status?: string
  payment_status?: string
  search?: string
  date_from?: string
  date_to?: string
}
