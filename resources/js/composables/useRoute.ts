import { route } from '@/helpers/route'
import type { RouteName } from '@/helpers/route'

// Export the route function directly for easier usage
export { route }
export type { RouteName }

// Composable for future use if needed
export function useRoute() {
    return route
}