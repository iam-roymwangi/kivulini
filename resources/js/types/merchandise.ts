export type MerchandiseType = 't_shirt' | 'hoodie' | 'hat' | 'other';
export type MerchandiseStatus = 'draft' | 'published';

export interface MerchandiseImage {
    id: number;
    url: string;
    file_path: string;
    is_cover: boolean;
    sort_order: number;
}

export interface MerchandiseItem {
    id: number;
    name: string;
    slug: string;
    type: MerchandiseType;
    description: string | null;
    price: string;
    sizes: string[];
    colors: string[];
    stock: number;
    status: MerchandiseStatus;
    cover_image_url: string | null;
    images: MerchandiseImage[];
}
