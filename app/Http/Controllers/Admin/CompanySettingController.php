<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingController extends Controller
{
    private array $settingDefinitions = [
        'branding' => [
            'company_name' => [
                'type' => 'text',
                'label' => 'Nama Perusahaan',
                'description' => 'Nama resmi perusahaan yang akan ditampilkan di aplikasi',
                'required' => true,
                'is_public' => true,
                'default' => 'PT Perusahaan Indonesia'
            ],
            'company_tagline' => [
                'type' => 'text',
                'label' => 'Tagline Perusahaan',
                'description' => 'Slogan atau tagline perusahaan',
                'required' => false,
                'is_public' => true,
            ],
            'logo' => [
                'type' => 'image',
                'label' => 'Logo Perusahaan',
                'description' => 'Logo utama perusahaan (format: PNG, JPG, SVG. Maksimal 2MB)',
                'required' => false,
                'is_public' => true,
            ],
            'logo_dark' => [
                'type' => 'image',
                'label' => 'Logo Dark Mode',
                'description' => 'Logo untuk tema gelap (format: PNG, JPG, SVG. Maksimal 2MB)',
                'required' => false,
                'is_public' => true,
            ],
            'favicon' => [
                'type' => 'image',
                'label' => 'Favicon',
                'description' => 'Icon untuk browser tab (format: ICO, PNG. Size: 32x32px)',
                'required' => false,
                'is_public' => true,
            ],
            'primary_color' => [
                'type' => 'color',
                'label' => 'Warna Utama',
                'description' => 'Warna utama untuk tema aplikasi',
                'required' => false,
                'is_public' => true,
                'default' => '#3b82f6'
            ],
        ],
        'company_info' => [
            'company_address' => [
                'type' => 'textarea',
                'label' => 'Alamat Perusahaan',
                'description' => 'Alamat lengkap kantor pusat perusahaan',
                'required' => false,
                'is_public' => true,
            ],
            'company_phone' => [
                'type' => 'text',
                'label' => 'Nomor Telepon',
                'description' => 'Nomor telepon kantor utama',
                'required' => false,
                'is_public' => true,
            ],
            'company_email' => [
                'type' => 'email',
                'label' => 'Email Perusahaan',
                'description' => 'Alamat email resmi perusahaan',
                'required' => false,
                'is_public' => true,
            ],
            'company_website' => [
                'type' => 'url',
                'label' => 'Website Perusahaan',
                'description' => 'URL website resmi perusahaan',
                'required' => false,
                'is_public' => true,
            ],
        ]
    ];

    public function index(): Response
    {
        // Get all current settings
        $currentSettings = CompanySetting::all()->keyBy('key');

        // Organize settings by group with definitions
        $settingsData = [];
        foreach ($this->settingDefinitions as $group => $settings) {
            $settingsData[$group] = [];
            foreach ($settings as $key => $definition) {
                $currentSetting = $currentSettings->get($key);
                $settingsData[$group][$key] = array_merge($definition, [
                    'key' => $key,
                    'current_value' => $currentSetting ? $currentSetting->value : ($definition['default'] ?? null),
                    'has_value' => (bool) $currentSetting,
                ]);
            }
        }

        return Inertia::render('admin/Settings/Index', [
            'settings' => $settingsData,
            'groups' => [
                'branding' => 'Branding & Identitas',
                'company_info' => 'Informasi Perusahaan',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $rules = [];
        $messages = [];

        // Build validation rules based on setting definitions
        foreach ($this->settingDefinitions as $group => $settings) {
            foreach ($settings as $key => $definition) {
                if ($request->has($key)) {
                    $fieldRules = [];

                    if ($definition['required'] ?? false) {
                        $fieldRules[] = 'required';
                    } else {
                        $fieldRules[] = 'nullable';
                    }

                    switch ($definition['type']) {
                        case 'email':
                            $fieldRules[] = 'email';
                            break;
                        case 'url':
                            $fieldRules[] = 'url';
                            break;
                        case 'image':
                            $fieldRules[] = 'image';
                            $fieldRules[] = 'max:2048'; // 2MB
                            break;
                        case 'color':
                            $fieldRules[] = 'regex:/^#[0-9A-F]{6}$/i';
                            break;
                        case 'text':
                        case 'textarea':
                            $fieldRules[] = 'string';
                            $fieldRules[] = 'max:1000';
                            break;
                    }

                    $rules[$key] = implode('|', $fieldRules);
                    $messages["{$key}.required"] = $definition['label'] . ' wajib diisi.';
                    $messages["{$key}.email"] = $definition['label'] . ' harus berupa alamat email yang valid.';
                    $messages["{$key}.url"] = $definition['label'] . ' harus berupa URL yang valid.';
                    $messages["{$key}.image"] = $definition['label'] . ' harus berupa file gambar.';
                    $messages["{$key}.max"] = $definition['label'] . ' maksimal 2MB.';
                    $messages["{$key}.regex"] = $definition['label'] . ' harus berupa kode warna hex yang valid.';
                }
            }
        }

        $validated = $request->validate($rules, $messages);

        foreach ($validated as $key => $value) {
            // Find the setting definition
            $definition = null;
            foreach ($this->settingDefinitions as $group => $settings) {
                if (isset($settings[$key])) {
                    $definition = $settings[$key];
                    $definition['group'] = $group;
                    break;
                }
            }

            if (!$definition) continue;

            // Handle file uploads
            if ($definition['type'] === 'image' && $request->hasFile($key)) {
                // Delete old file if exists
                $oldSetting = CompanySetting::where('key', $key)->first();
                if ($oldSetting && $oldSetting->getRawOriginal('value')) {
                    Storage::delete($oldSetting->getRawOriginal('value'));
                }

                // Store new file
                $path = $request->file($key)->store('company-settings', 'public');
                $value = $path;
            }

            // Save setting
            CompanySetting::set(
                $key,
                $value,
                $definition['type'],
                $definition['group'],
                $definition['description'] ?? null,
                $definition['is_public'] ?? false
            );
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function deleteFile(Request $request, string $key)
    {
        $request->validate([
            'key' => 'required|string'
        ]);

        $setting = CompanySetting::where('key', $key)->first();

        if (!$setting) {
            return back()->with('error', 'Setting tidak ditemukan.');
        }

        // Check if it's an image/file type
        if (!in_array($setting->type, ['image', 'file'])) {
            return back()->with('error', 'Setting ini bukan file.');
        }

        // Delete file from storage
        if ($setting->getRawOriginal('value')) {
            Storage::delete($setting->getRawOriginal('value'));
        }

        // Clear the setting value
        $setting->update(['value' => null]);

        return back()->with('success', 'File berhasil dihapus.');
    }
}