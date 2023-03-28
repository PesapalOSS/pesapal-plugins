<p class="payment_module">	
	<form id="aut" name="pesapal_form" action="{$module_dir}payment.php" method="post">
		{foreach from=$p key=k item=v}
		<input type="hidden" name="{$k}" value="{$v}" />
		{/foreach}
		<input type="image" value="{l s='Validate order' mod='pesapal'}" src="{$base_dir_ssl}modules/pesapal/logo_pesapal.jpg" border="0" style="border:0" />
	</form>
</p>