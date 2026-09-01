export interface EventMedia {
    id: number;
    file_path: string;
    type: 'image' | 'video';
    is_featured: boolean;
    sort_order: number;
    url: string;
}

export interface EventQuestion {
    id: number;
    question_text: string;
    type: 'text' | 'textarea' | 'select';
    options: string[] | null;
    is_required: boolean;
    sort_order: number;
}

export interface PlatformEvent {
    id: number;
    title: string;
    slug: string;
    type: 'cultural_heritage' | 'wildlife_safari' | 'food_music' | 'road_trip' | 'hiking' | 'vacation';
    summary: string;
    description: string;
    location: string;
    pickup_location: string | null;
    start_date: string;
    end_date: string;
    price: string;
    capacity: number;
    booked_slots: number;
    available_slots: number;
    status: 'draft' | 'published' | 'completed' | 'cancelled';
    liability_waiver_text: string | null;
    cover_image_url: string | null;
    media: EventMedia[];
    questions: EventQuestion[];
}

export interface BookingPayload {
    contact_name: string;
    contact_email: string;
    contact_phone: string;
    quantity: number;
    responses: { event_question_id: number; answer: string }[];
    consent: { agreed: boolean; signer_name: string };
}
