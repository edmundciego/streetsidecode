<div class="d-flex flex-row cart--table-scroll">
        <table class="table table-bordered">
            <thead class="text-muted thead-light">
                <tr class="text-center">
                    <th class="border-bottom-0" scope="col">{{translate('messages.item')}}</th>
                    <th class="border-bottom-0" scope="col">{{translate('messages.qty')}}</th>
                    <th class="border-bottom-0" scope="col">{{translate('messages.price')}}</th>
                    <th class="border-bottom-0" scope="col">{{translate('messages.delete')}}</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $subtotal = 0;
                $addon_price = 0;
                $tax = isset($store)? $store->tax: 0;
                $discount = 0;
                $discount_type = 'amount';
                $discount_on_product = 0;
                $variation_price  = 0;
            ?>
            @if(session()->has('cart') && count( session()->get('cart')) > 0)
                <?php
                    $cart = session()->get('cart');
                    if(isset($cart['tax']))
                    {
                        $tax = $cart['tax'];
                    }
                    if(isset($cart['discount']))
                    {
                        $discount = $cart['discount'];
                        $discount_type = $cart['discount_type'];
                    }
                ?>
                @foreach(session()->get('cart') as $key => $cartItem)
                @if(is_array($cartItem))
                    <?php
                    $variation_price += $cartItem['variation_price'] ?? 0;
                    $product_subtotal = ($cartItem['price'])*$cartItem['quantity'];
                    $discount_on_product += ($cartItem['discount']*$cartItem['quantity']);
                    $subtotal += $product_subtotal;
                    $addon_price += $cartItem['addon_price'];
                    ?>
                <tr>
                    <td class="media align-items-center cursor-pointer quick-View-Cart-Item" data-product-id="{{$cartItem['id']}}" data-item-key="{{$key}}">
                        <img class="avatar avatar-sm mr-1 onerror-image"
                        src="{{ \App\CentralLogics\Helpers::onerror_image_helper(
                            $cartItem['image'] ?? '',
                            asset('storage/app/public/product').'/'.$cartItem['image'] ?? '',
                            asset('public/assets/admin/img/100x100/2.png'),
                            'product/'
                        ) }}"

                                data-onerror-image="{{asset('public/assets/admin/img/100x100/2.png')}}" alt="{{$cartItem['name']}} image">
                        <div class="media-body">
                            <h5 class="text-hover-primary mb-0">{{Str::limit($cartItem['name'], 10)}}</h5>
                            <small>{{Str::limit($cartItem['variant'], 20)}}</small>
                        </div>
                    </td>
                    <td class="text-center middle-align">
                        <input type="number"  data-key="{{$key}}" class="amount--input form-control text-center update-Quantity" value="{{$cartItem['quantity']}}" min="1" max="{{$cartItem['maximum_cart_quantity']?? '9999999999'}}">
                    </td>
                    <td class="text-center px-0 py-1">
                        <div class="btn">
                            {{\App\CentralLogics\Helpers::format_currency($product_subtotal)}}
                        </div> <!-- price-wrap .// -->
                    </td>
                    <td class="align-items-center text-center">
                        <a href="javascript:" data-product-id="{{$key}}" class="btn btn-sm btn-outline-danger remove-From-Cart"> <i class="tio-delete-outlined"></i></a>
                    </td>
                </tr>
                @endif
                @endforeach
            @endif
            </tbody>
        </table>
    </div>

    <?php
        if(session()->get('address') && count(session()->get('address'))>0){
            $delivery_fee = session()->get('address')['delivery_fee'];
        }else{
            $delivery_fee = 0;
        }
        $total = $subtotal+$addon_price;
        $discount_amount = ($discount_type=='percent' && $discount>0)?((($total-$discount_on_product) * $discount)/100):$discount;
        $total -= ($discount_amount + $discount_on_product);
        $tax_included = \App\Models\BusinessSetting::where(['key'=>'tax_included'])->first() ?  \App\Models\BusinessSetting::where(['key'=>'tax_included'])->first()->value : 0;
        $total_tax_amount= ($tax > 0)?(($total * $tax)/100):0;
        $total = $total + $delivery_fee;
    ?>
    <div class="box p-3">
        <dl class="row text-sm-right">

            <dt  class="col-sm-6">{{translate('messages.addon')}}:</dt>
            <dd class="col-sm-6 text-right">{{\App\CentralLogics\Helpers::format_currency($addon_price)}}</dd>

            <dt  class="col-sm-6">{{translate('messages.subtotal')}}
                @if ($tax_included ==  1)
                ({{ translate('messages.TAX_Included') }})
                @php($total_tax_amount=0)
                @endif
                :</dt>
            <dd class="col-sm-6 text-right">{{\App\CentralLogics\Helpers::format_currency($subtotal+$addon_price)}}</dd>


            <dt  class="col-sm-6">{{translate('messages.discount')}} :</dt>
            <dd class="col-sm-6 text-right">- {{\App\CentralLogics\Helpers::format_currency(round($discount_on_product,2))}}</dd>
            <dt class="col-6">{{ translate('messages.delivery_fee') }} :</dt>
            <dd class="col-6 text-right" id="delivery_price">
                {{ \App\CentralLogics\Helpers::format_currency($delivery_fee) }}</dd>
                @if ($tax_included !=  1)

            <dt  class="col-sm-6">{{ translate('messages.tax') }}  : </dt>
            <dd class="col-sm-6 text-right">
                {{\App\CentralLogics\Helpers::format_currency(round($total_tax_amount,2))}}</dd>
      @endif
            <dt  class="col-6 pr-0">
                <hr class="mt-0">
            </dt>
            <dt  class="col-6 pl-0">
                <hr class="mt-0">
            </dt>
            <dt  class="col-sm-6">{{ translate('messages.total') }}  : </dt>
            <dd class="col-sm-6 text-right">
                {{\App\CentralLogics\Helpers::format_currency(round($total+$total_tax_amount, 2))}}
            </dd>

        </dl>
        <form action="{{route('admin.pos.order')}}?store_id={{request('store_id')}}" id='order_place' method="post" >
            @csrf
            <input type="hidden" name="user_id" id="customer_id">
            <div class="pos--payment-options mt-3 mb-3">
                <h5 class="mb-3">{{ translate('Payment Method') }}</h5>
                <ul>
                    @php($cod=\App\CentralLogics\Helpers::get_business_settings('cash_on_delivery'))
                    @if ($cod['status'])
                    <li>
                        <label>
                            <input type="radio" name="type" value="cash" hidden checked>
                            <span>{{ translate('Cash On Delivery') }}</span>
                        </label>
                    </li>
                    @endif
                    @php($wallet=\App\CentralLogics\Helpers::get_business_settings('wallet_status'))
                    @if ($wallet)
                    <li>
                        <label>
                            <input type="radio" name="type" value="wallet" hidden {{ $cod['status']? '':'checked' }}>
                            <span>{{ translate('Wallet') }}</span>
                        </label>
                    </li>
                    @endif
                </ul>
            </div>

        <div class="row button--bottom-fixed g-1 bg-white">
            <div class="col-sm-6">
                <button type="submit" class="btn  btn--primary btn-sm place-order-submit btn-block">{{ translate('messages.place_order') }} </button>
            </div>
            <div class="col-sm-6">
                <a href="#" class="btn btn--reset btn-sm btn-block empty-Cart" >{{  translate('Clear Cart') }}</a>
            </div>
        </div>
        </form>
    </div>


    <div class="modal fade" id="deliveryAddrModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom py-3">
                    <h5 class="modal-title flex-grow-1 text-center">{{translate('delivery_options')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                @if ($store)
                <div class="modal-body">

                    <?php
                        if(session()->has('address')) {
                            $old = session()->get('address');
                        }else {
                            $old = null;
                        }
                    ?>
                    <form id='delivery_address_store'>
                        @csrf

                        <div class="row g-2" id="delivery_address">
                            <div class="col-md-6">
                                <label class="input-label"
                                    for="contact_person_name">{{ translate('messages.contact_person_name') }}<span
                                                class="input-label-secondary text-danger">*</span></label>
                                <input  id="contact_person_name" type="text" class="form-control" name="contact_person_name"
                                    value="{{ $old ? $old['contact_person_name'] : '' }}" placeholder="{{ translate('messages.Ex :') }} Jhone">
                            </div>
                            <div class="col-md-6">
                                <label class="input-label"
                                    for="contact_person_number">{{ translate('Contact Number') }}<span
                                                class="input-label-secondary text-danger">*</span></label>
                                <input id="contact_person_number" type="tel" class="form-control" name="contact_person_number"
                                    value="{{ $old ? $old['contact_person_number'] : '' }}"  placeholder="{{ translate('messages.Ex :') }} +3264124565">
                            </div>
                            <div class="col-md-4">
                                <label class="input-label" for="road">{{ translate('messages.Road') }}<span
                                                class="input-label-secondary text-danger">*</span></label>
                                <input id="road" type="text" class="form-control" name="road" value="{{ $old ? $old['road'] : '' }}"  placeholder="{{ translate('messages.Ex :') }} 4th">
                            </div>
                            <div class="col-md-4">
                                <label  class="input-label" for="house">{{ translate('messages.House') }}<span
                                                class="input-label-secondary text-danger">*</span></label>
                                <input id="house" type="text" class="form-control" name="house" value="{{ $old ? $old['house'] : '' }}" placeholder="{{ translate('messages.Ex :') }} 45/C">
                            </div>
                            <div class="col-md-4">
                                <label class="input-label" for="floor">{{ translate('messages.Floor') }}<span
                                                class="input-label-secondary text-danger">*</span></label>
                                <input id="floor" type="text" class="form-control" name="floor" value="{{ $old ? $old['floor'] : '' }}"  placeholder="{{ translate('messages.Ex :') }} 1A">
                            </div>
                            <div class="col-md-6">
                                <label class="input-label" for="longitude">{{ translate('messages.longitude') }}<span
                                                class="input-label-secondary text-danger">*</span></label>
                                <input  type="text" class="form-control" id="longitude" name="longitude"
                                    value="{{ $old ? $old['longitude'] : '' }}" readonly >
                            </div>
                            <div class="col-md-6">
                                <label class="input-label" for="latitude">{{ translate('messages.latitude') }}<span
                                                class="input-label-secondary text-danger">*</span></label>
                                <input  type="text" class="form-control" id="latitude" name="latitude"
                                    value="{{ $old ? $old['latitude'] : '' }}" readonly>
                            </div>
                            <div class="col-md-12">
                                <label class="input-label" for="address">{{ translate('messages.address') }}</label>
                                <textarea id="address" name="address" class="form-control" cols="30" rows="3" placeholder="{{ translate('messages.Ex :') }} address">{{ $old ? $old['address'] : '' }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <span class="text-primary">
                                        {{ translate('* pin the address in the map to calculate delivery fee') }}
                                    </span>
                                    <div>
                                        <input type="hidden" name="distance" id="distance">
                                        <span>{{ translate('Delivery fee') }} :</span>
                                        <input type="hidden" name="delivery_fee" id="delivery_fee" value="{{ $old ? $old['delivery_fee'] : '' }}">
                                        <strong>{{ $old ? $old['delivery_fee'] : 0 }} {{ \App\CentralLogics\Helpers::currency_symbol() }}</strong>
                                    </div>
                                </div>
                                <input id="pac-input" class="controls rounded initial-8"
                                    title="{{ translate('messages.search_your_location_here') }}" type="text"
                                    placeholder="{{ translate('messages.search_here') }}" />
                                <div class="mb-2 h-200px" id="map"></div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="btn--container justify-content-end">
                                <button class="btn btn-sm btn--primary w-100 delivery-Address-Store" type="button">
                                    {{  translate('Update_Delivery address') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @else
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center">
                                <h2>
                                    {{translate('messages.please_select_a_store_first')}}
                                </h2>
                                <button data-dismiss="modal" class="btn btn-primary">{{translate('messages.Ok')}}</button>
                            </div>
                        </div>
                    </div>
                </div>

                @endif
            </div>
        </div>
    </div>


    <script src="{{asset('public/assets/admin')}}/js/view-pages/common.js"></script>






