 <!-- Today sales -->
 <div class="row">
     <div class="col-md-8">
         <div class="table-container">
             <table class="table">
                 <thead>
                     <th>#</th>
                     <th>المنتج</th>
                     <th>العيار</th>
                     <th>الصنف</th>
                     <th>سعر الشراء</th>
                     <th>الوزن</th>
                     <th>سعر الغرام</th>
                     <th>عيار 24</th>
                     <th></th>
                 </thead>
                 <tbody>
                     @php
                         $totalPrice = 0;
                         $totalWeightQuantity = 0;
                      
                     @endphp
                     @foreach ($newboughtedQuantity as $weightQuantity)
                         @php
                             $totalPrice += $weightQuantity->price;
                             $totalWeightQuantity += $weightQuantity->weight;
                             if (Auth::user()->type == 1 || Auth::user()->type == 3) {
                             $route = route('quantities.detail', $weightQuantity->id);
                         } else {
                             $route = route('quantities.entry.detail', $weightQuantity->id);
                         }
                         @endphp
                         <tr class="text-{{ !empty($weightQuantity->notice) ? 'success' : '' }}">
                             <td>{{ $loop->iteration }}</td> 
                             <td>{{ $weightQuantity->quantity->short_ident }}</td>
                             <td>{{ $weightQuantity->quantity->caliber->full_name }}</td>
                             <td>{{ $weightQuantity->quantity->type->name }}</td>
                             <td>{{ $weightQuantity->price }}</td>
                             <td>{{ $weightQuantity->weight }} g</td>
                             <td>{{ number_format($weightQuantity->price / $weightQuantity->weight, 2) }} </td>
                             <td>{{ $weightQuantity->weight * $weightQuantity->quantity->caliber->transfarmed }}</td>
                             <td>{{ $weightQuantity->user->name }}</td>
                             <td><a href="{{ $route }}">تفاصيل</a></td>
                         </tr>
                     @endforeach

                 </tbody>
             </table>
             <span>سعر الشراء الإجمالي: {{ $totalPrice }} <b>€</b> ------ </span>
             <span> الوزن الاجمالي : {{ $totalWeightQuantity }} <b>g</b></span>
         </div>
     </div>
 </div>
