import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useCompanySettings() {
    const page = usePage()

    // Get settings from page props or use defaults
    const settings = computed(() => {
        const pageSettings = page.props.companySettings as Record<string, any> || {}

        return {
            companyName: pageSettings.company_name || 'PT Perusahaan Indonesia',
            companyTagline: pageSettings.company_tagline || 'Human Resource Management System',
            companyLogo: pageSettings.logo || null,
            companyLogoDark: pageSettings.logo_dark || null,
            companyFavicon: pageSettings.favicon || null,
            primaryColor: pageSettings.primary_color || '#3b82f6',
            companyAddress: pageSettings.company_address || '',
            companyPhone: pageSettings.company_phone || '',
            companyEmail: pageSettings.company_email || '',
            companyWebsite: pageSettings.company_website || '',
        }
    })

    // Individual getters for easier access
    const companyName = computed(() => settings.value.companyName)
    const companyTagline = computed(() => settings.value.companyTagline)
    const companyLogo = computed(() => settings.value.companyLogo)
    const companyLogoDark = computed(() => settings.value.companyLogoDark)
    const companyFavicon = computed(() => settings.value.companyFavicon)
    const primaryColor = computed(() => settings.value.primaryColor)
    const companyAddress = computed(() => settings.value.companyAddress)
    const companyPhone = computed(() => settings.value.companyPhone)
    const companyEmail = computed(() => settings.value.companyEmail)
    const companyWebsite = computed(() => settings.value.companyWebsite)

    // Get the appropriate logo based on dark mode
    const getCurrentLogo = (isDark = false) => {
        if (isDark && companyLogoDark.value) {
            return companyLogoDark.value
        }
        return companyLogo.value
    }

    return {
        settings,
        companyName,
        companyTagline,
        companyLogo,
        companyLogoDark,
        companyFavicon,
        primaryColor,
        companyAddress,
        companyPhone,
        companyEmail,
        companyWebsite,
        getCurrentLogo,
    }
}