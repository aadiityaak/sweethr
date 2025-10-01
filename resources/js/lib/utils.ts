import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(urlToCheck: NonNullable<InertiaLinkProps['href']>, currentUrl: string) {
    const targetUrl = toUrl(urlToCheck);
    
    // Remove query parameters and hash from both URLs for comparison
    const cleanTargetUrl = targetUrl.split('?')[0].split('#')[0];
    const cleanCurrentUrl = currentUrl.split('?')[0].split('#')[0];
    
    return cleanTargetUrl === cleanCurrentUrl;
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
