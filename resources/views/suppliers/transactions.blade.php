@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('suppliers.index') }}"> الموردون - الرئيسية</a>
        <h2>تفاصيل عمليات المورد: {{ $supplier->name }}</h2>
        <hr>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>نوع العملية</th>
                    <th>الحركة</th>
                    <th>القيمة</th>
                    <th>الموظف المسؤول</th>
                    <th>تاريخ الإنشاء</th>
                    <th>سعر تثبيت الذهب</th>
                    <th>الوزن المطلوب</th>
                    <th>الوزن المستلم</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->type == 1 ? 'مال' : 'ذهب' }}</td>
                        <td>{{ $transaction->amount < 0 ? 'إرسال' : 'استلام' }}</td>
                        <td>
                            {{ abs($transaction->amount) }}
                            {{ $transaction->type == 1 ? '€' : 'غرام' }}
                        </td>
                        <td>{{ $transaction->user->name ?? 'غير متاح' }}</td>
                        <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            @if ($transaction->price_per_gram)
                                <span style="color: green;">
                                    {{ $transaction->price_per_gram }} €
                                </span>
                            @else
                                <span style="color: red;">
                                    غير متاح
                                </span>
                            @endif
                        </td>
                        <td>{{$transaction->type == 1 ? $transaction->expected_weight : '-' }} غ</td>
                        <td>{{ $transaction->type == 1 ? $transaction->received_weight : '-' }} غ</td>
                        <td>{{ $transaction->status == 1 ? 'معلق' : 'مكتمل' }}</td>
                        <td>
                            @if (is_null($transaction->price_per_gram))
                                <button class="btn btn-warning btn-sm"
                                    onclick="openFixPriceModal({{ $transaction->id }})">تثبيت</button>
                            @endif
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>



        <!-- Display pagination links -->
        <div class="d-flex justify-content-center">
            {{ $transactions->links() }}
        </div>

        <!-- موديل تثبيت السعر -->
        <div class="modal fade" id="fixPriceModal" tabindex="-1" aria-labelledby="fixPriceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="fixPriceForm">
                        <div class="modal-header">
                            <h5 class="modal-title" id="fixPriceModalLabel">تثبيت سعر الذهب</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="transactionId" name="transaction_id">
                            <div class="form-group">
                                <label for="fixPrice">سعر الغرام</label>
                                <input type="number" step="0.01" class="form-control" id="fixPrice"
                                    name="price_per_gram" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
                            <button type="submit" class="btn btn-primary">تثبيت</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')

<script>
    function openFixPriceModal(transactionId) {
    $('#transactionId').val(transactionId);
    $('#fixPriceModal').modal('show');
}
$('#fixPriceForm').on('submit', function(e) {
    e.preventDefault();

    // الحصول على البيانات المطلوبة
    let pricePerGram = $('#fixPrice').val();

    // تأكيد العملية باستخدام alert
    if (confirm('هل أنت متأكد من أنك تريد تثبيت السعر بـ ' + pricePerGram + ' €؟')) {
        // تعطيل زر الحفظ
        $('#fixPriceForm button[type="submit"]').prop('disabled', true);

        // بيانات النموذج
        let formData = $(this).serialize();

        $.ajax({
            url: '{{ route('supplierTransactions.fixPrice') }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                alert('تم تثبيت السعر بنجاح.');
                location.reload(); // تحديث الصفحة
            },
            error: function(error) {
                alert('حدث خطأ أثناء تثبيت السعر.');
                // إعادة تفعيل زر الحفظ
                $('#fixPriceForm button[type="submit"]').prop('disabled', false);
            }
        });
    }
});


</script>
@endsection
