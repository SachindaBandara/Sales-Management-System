// types/index.ts

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    created_at: string;
    updated_at: string;
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    products_count: number;
    created_at: string;
    updated_at: string;
}

export interface Brand {
    id: number;
    name: string;
    slug: string;
    description?: string;
    products_count: number;
    created_at: string;
    updated_at: string;
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
    stock_quantity?: number;
    sku?: string;
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
    page?: number;
}

export interface CartItem {
    id: number;
    product_id: number;
    product: Product;
    quantity: number;
    price: number;
    total: number;
}

export interface Cart {
    items: CartItem[];
    total_items: number;
    total_amount: number;
}

// Flash message types
export interface FlashMessages {
    success?: string;
    error?: string;
    info?: string;
    warning?: string;
}

// Inertia page props type
export interface PageProps {
    user: User;
    flash: FlashMessages;
    [key: string]: any;
}