export type FollowUpStatus = 'new' | 'contacted' | 'follow_up' | 'resolved';

export type BookingPaymentStatus = 'pending' | 'paid' | 'failed' | 'cancelled' | 'refunded';

export interface FollowUpContact {
    booking_reference: string;
    contact_name: string;
    contact_email: string;
    contact_phone: string;
    payment_status: BookingPaymentStatus;
    event: { title: string } | null;
}

export const followUpLabels: Record<FollowUpStatus, string> = {
    new: 'Not contacted',
    contacted: 'Contacted',
    follow_up: 'Needs follow-up',
    resolved: 'Resolved',
};

export const followUpClasses: Record<FollowUpStatus, string> = {
    new: 'bg-muted text-muted-foreground',
    contacted: 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
    follow_up: 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
    resolved: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
};

/**
 * Convert a Kenyan number (07…, 01…, +254…, 254…) into the international
 * digits-only format WhatsApp links expect. Returns null when unusable.
 */
export function toWhatsAppNumber(phone: string): string | null {
    const digits = phone.replace(/\D/g, '');

    if (digits.startsWith('254') && digits.length === 12) {
        return digits;
    }

    if (digits.startsWith('0') && digits.length === 10) {
        return `254${digits.slice(1)}`;
    }

    if (digits.length === 9) {
        return `254${digits}`;
    }

    return digits.length >= 10 ? digits : null;
}

/**
 * A ready-to-send message matched to where the customer is in the payment flow.
 */
export function followUpMessage(booking: FollowUpContact): string {
    const firstName = booking.contact_name.trim().split(/\s+/)[0] ?? '';
    const trip = booking.event?.title ?? 'your Kivulini trip';
    const ref = booking.booking_reference;

    switch (booking.payment_status) {
        case 'paid':
            return `Hi ${firstName}, this is Kivulini Adventures. Thank you for booking ${trip} (ref ${ref}). Your payment is confirmed. Do you have any questions before the trip?`;
        case 'failed':
            return `Hi ${firstName}, this is Kivulini Adventures. We noticed your M-Pesa payment for ${trip} (ref ${ref}) didn't go through. Would you like help completing your booking?`;
        case 'cancelled':
        case 'refunded':
            return `Hi ${firstName}, this is Kivulini Adventures following up on your booking for ${trip} (ref ${ref}). Is there anything we can help with?`;
        default:
            return `Hi ${firstName}, this is Kivulini Adventures. Your booking for ${trip} (ref ${ref}) is waiting for M-Pesa payment. Need any help completing it?`;
    }
}

export function whatsAppLink(booking: FollowUpContact): string | null {
    const number = toWhatsAppNumber(booking.contact_phone);

    return number ? `https://wa.me/${number}?text=${encodeURIComponent(followUpMessage(booking))}` : null;
}

export function emailLink(booking: FollowUpContact): string {
    const subject = `Your Kivulini booking ${booking.booking_reference}`;

    return `mailto:${booking.contact_email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(followUpMessage(booking))}`;
}

export function callLink(booking: FollowUpContact): string {
    return `tel:${booking.contact_phone.replace(/\s+/g, '')}`;
}
