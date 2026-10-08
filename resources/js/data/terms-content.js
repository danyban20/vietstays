// Text for the public /terms page. Edit the wording here; each section's
// `id` is its anchor (/terms#cancellation), which checkout links to.

export const TERMS_UPDATED = '8 October 2026';

export const TERMS_SECTIONS = [
    {
        id: 'booking',
        title: 'Booking and payment',
        paragraphs: [
            'When you book on Vietstays you send a booking request for the dates and apartment you chose. You get an email with your booking reference straight away.',
            'The host then confirms or declines the request. We email you as soon as that happens. Your stay is only guaranteed once it is confirmed.',
            'Prices are shown in Vietnamese dong (VND) and include the Vietstays booking fee. You pay on arrival at check-in. Online card payment is not available yet.',
        ],
    },
    {
        id: 'cancellation',
        title: 'Cancellation and changes',
        paragraphs: [
            'Because you pay on arrival, we do not charge you for cancelling before check-in. Please tell us as early as possible so the apartment can be offered to other guests.',
            'To cancel or change your dates, contact us with your booking reference. We email you to confirm any change or cancellation.',
            'If a host has to cancel a confirmed booking, we email you right away and help you find another stay.',
        ],
    },
    {
        id: 'privacy',
        title: 'Privacy',
        paragraphs: [
            'We collect the details you give us when you book or create an account: your name, email address, phone number and the details of your stay.',
            'We use them to handle your booking, to send you emails about it, and to let the host of your apartment prepare for your arrival. The host sees your name, contact details and booking details.',
            'We do not sell your personal data. To see, correct or delete the data we hold about you, contact us.',
        ],
    },
];
