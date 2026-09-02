export type Appearance = 'light' | 'dark' | 'system';
export type ResolvedAppearance = 'light' | 'dark';

export type AppVariant = 'header' | 'sidebar';

export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    links?: PaginationLink[];
    path?: string;
    per_page: number;
    to: number | null;
    total: number;
}

export interface Paginator<T> {
    data: T[];
    links?: PaginationLink[];
    meta?: PaginationMeta;
    current_page?: number;
    last_page?: number;
    total?: number;
    per_page?: number;
    from?: number | null;
    to?: number | null;
}
