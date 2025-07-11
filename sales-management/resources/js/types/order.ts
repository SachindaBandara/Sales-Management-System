export interface OrderItem {
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

export interface User {
    first_name?: string;
    last_name?: string;
    email?: string;
    phone?: string;
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

export interface OrderItem {
  id: number;
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
  status: string;
  payment_status: string;
  payment_method: string;
  total_amount: number;
  subtotal: number;
  tax_amount: number;
  shipping_amount: number;
  discount_amount: number;
  created_at: string;
  updated_at: string;
  billing_address: Address;
  shipping_address: Address;
  notes?: string;
  items: OrderItem[];
  user: User;
}
