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
                     <th>الوزن</th>
                     <th>عيار 24</th>
                     <th>نوع العملية</th>
                     <th></th>
                 </thead>
                 <tbody>
                    @php
                        $totalWeight = 0 ;
                    @endphp
                     @foreach ($newProducts as $selled_product)
                      @php
                          $totalWeight += $selled_product->weight
                      @endphp 
                         <tr class="text-{{ !empty($selled_product->description) ? 'success' : '' }}">
                             <td>{{ $loop->iteration }}</td>
                             <td>{{ $selled_product->short_ident }}</td>
                             <td>{{ $selled_product->caliber->full_name }}</td>
                             <td>{{ $selled_product->type->name }}</td>
                             <td>{{ $selled_product->weight }} g</td>
                             <td>{{ $selled_product->weight * $selled_product->caliber->transfarmed }} g</td>
                             <td>منتج جديد</td>
                             <td><a target="_blank" href="{{ route('products.show', $selled_product->id) }}">تفاصيل</a>
                                 {{-- <td>
                                @if ($selled_product->short_ident)
                                    <form class="m-0" action="{{ route('product_reset') }}" method="post">
                                        @csrf
                                        <input type="hidden" name="product" value="{{ $selled_product->id }}">
                                        <button class="btn btn-link text-danger p-0 m-0">الغاء</button>
                                    </form>
                                @endif
                            </td> --}}
                             </td>
                         </tr>
                     @endforeach
                     @foreach ($newQuantities as $quantity)
                         @foreach ($quantity->weightQuantities as $weightQuantity)
                             @if ($weightQuantity->status == 4)
                             @php
                                 $totalWeight +=  $weightQuantity->weight ;
                             @endphp 
                                 <tr class="text-{{ !empty($selled_product->description) ? 'success' : '' }}">
                                     <td>{{ $loop->iteration }}</td>
                                     <td>{{ $quantity->short_ident }}</td>
                                     <td>{{ $quantity->caliber->full_name }}</td>
                                     <td>{{ $quantity->type->name }}</td>
                                     <td>{{ $weightQuantity->weight }} g</td>
                                     <td>{{ $weightQuantity->weight * $quantity->caliber->transfarmed }} g</td>
                                     <td>{{ getStatusName($weightQuantity->status) }}</td>
                                     <td>{{ $weightQuantity->user->name }}</td>

                                 </tr>
                             @endif
                         @endforeach
                     @endforeach
                 </tbody>
             </table>
             <span>  الوزن الكلي: {{ $totalWeight }} <b>g</b></span>
         </div>
     </div>
 </div>
