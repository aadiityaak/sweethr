<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'max:'.($this->getDocumentType()?->max_file_size_mb ?? 10) * 1024],
            'issued_date' => ['nullable', 'date', 'before_or_equal:today'],
            'expiry_date' => ['nullable', 'date', 'after:issued_date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Karyawan harus dipilih.',
            'user_id.exists' => 'Karyawan yang dipilih tidak valid.',
            'document_type_id.required' => 'Jenis dokumen harus dipilih.',
            'document_type_id.exists' => 'Jenis dokumen yang dipilih tidak valid.',
            'title.required' => 'Judul dokumen harus diisi.',
            'title.max' => 'Judul dokumen maksimal 255 karakter.',
            'file.required' => 'File dokumen harus diupload.',
            'file.file' => 'File yang diupload harus berupa file.',
            'file.max' => 'Ukuran file maksimal :max KB.',
            'issued_date.date' => 'Tanggal terbit harus berupa tanggal yang valid.',
            'issued_date.before_or_equal' => 'Tanggal terbit tidak boleh lebih dari hari ini.',
            'expiry_date.date' => 'Tanggal kadaluarsa harus berupa tanggal yang valid.',
            'expiry_date.after' => 'Tanggal kadaluarsa harus setelah tanggal terbit.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $documentType = $this->getDocumentType();

            if ($documentType && $this->hasFile('file')) {
                $file = $this->file('file');
                $allowedExtensions = $documentType->allowed_extensions ?? [];

                if (! empty($allowedExtensions) && ! in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
                    $validator->errors()->add('file', 'File harus berformat: '.implode(', ', $allowedExtensions));
                }
            }
        });
    }

    private function getDocumentType()
    {
        if ($this->filled('document_type_id')) {
            return \App\Models\DocumentType::find($this->input('document_type_id'));
        }

        return null;
    }
}
