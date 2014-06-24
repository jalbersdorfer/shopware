{namespace name="frontend/checkout/cart_item"}

<div class="table--row block-group row--rebate">

    {* Product information column *}
    {block name='frontend_checkout_cart_item_rebate_name'}
        <div class="table--column column--product block">

			{* Badge *}
			{block name='frontend_checkout_cart_item_rebate_badge'}
				<div class="table--media basket--badge">
					<i class="icon--arrow-right"></i>
				</div>
			{/block}

            {* Product information *}
            {block name='frontend_checkout_cart_item_rebate_details'}
                <div class="table--content">

                    {* Product name *}
                    {block name='frontend_checkout_cart_item_rebate_details_title'}
                        <span class="content--title">{$sBasketItem.articlename|strip_tags|truncate:60}</span>
                    {/block}

                    {* Product SKU number *}
                    {block name='frontend_checkout_cart_item_rebate_details_sku'}
                        <p class="content--sku">
                            {s name="CartItemInfoId"}{/s} {$sBasketItem.ordernumber}
                        </p>
                    {/block}

                    {* Additional product information *}
                    {block name='frontend_checkout_cart_item_rebate_details_inline'}{/block}
                </div>
            {/block}
        </div>
    {/block}

    {* Product tax rate *}
    {block name='frontend_checkout_cart_item_rebate_tax_price'}{/block}

    {* Accumulated product price *}
    {block name='frontend_checkout_cart_item_rebate_total_sum'}
        <div class="table--column column--total-price block is--align-right">
			{block name='frontend_checkout_cart_item_rebate_total_sum_label'}
				<div class="column--label total-price--label">
					{s name="CartColumnTotal" namespace="frontend/checkout/cart_header"}{/s}
				</div>
			{/block}

            {if $sBasketItem.itemInfo}
                {$sBasketItem.itemInfo}
            {else}
                {$sBasketItem.price|currency} {block name='frontend_checkout_cart_tax_symbol'}{s name="Star" namespace="frontend/listing/box_article"}{/s}{/block}
            {/if}
        </div>
    {/block}
</div>