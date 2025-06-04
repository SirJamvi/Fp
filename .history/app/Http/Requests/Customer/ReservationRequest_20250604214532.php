namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waktu_kedatangan' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after_or_equal:' . Carbon::now()->addMinutes(15)->format('Y-m-d H:i:s'),
            ],
            'jumlah_tamu' => 'required|integer|min:1|max:20',
            'catatan' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'waktu_kedatangan.required' => 'Waktu kedatangan harus diisi.',
            'waktu_kedatangan.date_format' => 'Format waktu kedatangan tidak valid.',
            'waktu_kedatangan.after_or_equal' => 'Waktu kedatangan minimal 15 menit dari sekarang.',
            'jumlah_tamu.required' => 'Jumlah tamu harus diisi.',
            'jumlah_tamu.integer' => 'Jumlah tamu harus berupa angka.',
            'jumlah_tamu.min' => 'Jumlah tamu minimal 1 orang.',
            'jumlah_tamu.max' => 'Jumlah tamu maksimal 20 orang.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}