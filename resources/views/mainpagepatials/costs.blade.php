<div class="row">
    <div class="col-md-8">
        <div class="table-container">
            <table class="table">
                <thead>
                    <th>#</th>
                    <th>النوع</th>
                    <th>القيمة</th>
                    <th>العملية</th>
                    <th>المدخل</th>
                    <th>ملاحظة</th>
                </thead>
                <tbody>
                    @foreach ($costs as $cost)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $cost->costType->type }}</td>
                            <td class="text-{{ $cost->cost_value > 0 ? 'success' : 'danger' }}">{{ $cost->cost_value  }} <b>€</b></td>
                            <td>{{ $cost->cost_value > 0 ? 'إيداع' : 'سحب' }}</td>
                            <td>{{ $cost->user->name }}</td>
                            <td>{{ $cost->note }}</td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>
