{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{extends file=$layout}

{block name='content'}

  <section id="main">
    <div class="cart-grid row">

      <!-- Left Block: cart product informations & shpping -->
      <div class="cart-grid-body col-xs-12 col-lg-8">

          <section>
            <iframe src="{$url}" width='100%' height='620px' scrolling='no' frameBorder='0'>
                <p>Browser unable to load iFrame</p>
            </iframe>
          </section>

      </div>

      <!-- Right Block: cart subtotal & cart total -->
      <div class="cart-grid-right col-xs-12 col-lg-4">

        <div class="card cart-summary">
            <div class="cart-detailed-totals">
            
                <div class="card-block">
                    <div class="cart-summary-line" id="cart-subtotal-products">
                        <span class="label js-subtotal">
                              {$ordered_items} Items
                        </span>
                        <span class="value">{$order_currency|cat:''}{$ordered_items_total|string_format:"%.2f"}</span>
                    </div>
                    <div class="cart-summary-line" id="cart-subtotal-shipping">
                        <span class="label">
                          Shipping
                        </span>
                        <span class="value">{$order_currency|cat:''}{$order_shipping|string_format:"%.2f"}</span>
                        
                  </div>
                </div>

                <hr>

                <div class="card-block">

                  <div class="cart-summary-line cart-total">
                    <span class="label">Total (tax incl.)</span>

                    <span class="value">{$order_currency|cat:''}{math equation="x + y" x=$ordered_items_total y=$order_shipping format="%.2f"}</span>
                  </div>

                </div>

            </div>
            
        </div>

      </div>

    </div>
  </section>
{/block}
