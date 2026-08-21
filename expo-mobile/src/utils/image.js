import { BASE_URL } from '../api/client';

export const getImageUrl = (image) => {
    if (!image) return null;
    if (String(image).startsWith('http')) return image;
    return `${BASE_URL}/storage/${image}`;
};
