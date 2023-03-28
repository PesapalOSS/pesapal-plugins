<?php

/**
 * @file
 * Contains \Drupal\uc_pesapal\Plugin\Ubercart\PaymentMethod\Pesapal.
 */

namespace Drupal\uc_pesapal\Plugin\Ubercart\PaymentMethod;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\uc_order\OrderInterface;
use Drupal\uc_payment\PaymentMethodPluginBase;

// ********* DO NOT DELETE COMMENT SECTION BELOW *****
// It is configuration information
// ______________________________________________

/**
 * Defines the pesapal payment method.
 *
 * @UbercartPaymentMethod(
 *  id = "pesapal",
 *  name = @Translation("Pesapal"),
 *  redirect = "\Drupal\uc_pesapal\Form\PesapalForm",
 *  title = @Translation("Pesapal: simple, secure, reliable.."),
 *  review = @Translation("Pesapal"),
 *  desc = @Translation("Use Pesapal e-wallet, MPesa, Yu Cash, Airtel Money, Eazy 24/7, cooperative bank, visa, mastercard and so much more to pay."),
 *  weight = 3,
 *  checkout = TRUE,
 *  no_gateway = TRUE,
 *  )
 */
 
class Pesapal extends PaymentMethodPluginBase {

    //redirect => "\Drupal\uc_pesapal\Form\PesapalForm";
    //$module_config = \Drupal::config('uc_pesapal.settings');
    //$title = $module_config->get('method_title');
    //$title = $this->t('Pesapal: simple, secure, reliable..');
//    	$title .= '<br />' . theme('image', array(
//    		'uri' => drupal_get_path('module', 'uc_pesapal') . '/images/pesapal-card-sm.png',
//    		'attributes' => array('class' => array('uc-pesapal-logo')),
//    		)
//    	);

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'uc_pesapal_is_live' => '0',
      'uc_pesapal_consumer_key' => '',
      'uc_pesapal_consumer_secret' => '',
//      'uc_pesapal_reference_code' => '',
//      'uc_pesapal_desc' => '',
//      'uc_pesapal_policy' => '',
//      'uc_pesapal_completed_msg' => '',
//      'uc_pesapal_pending_msg' => '',
//      'uc_pesapal_failed_msg' => '',
//      'uc_pesapal_invalid_msg' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    global $base_path,$base_url;
	
	
    # the options to display in our form radio buttons
    $options = array(
        '0' => $this->t('Demo'),
        '1' => $this->t('Live'),
    );
	
	//$scheme = \Drupal::request()->getScheme();
	//$host = \Drupal::request()->getHost();
	
	$ipn_url = \Drupal::request()->getSchemeAndHttpHost().Url::fromRoute('uc_pesapal.inpreturn')->toString();
	
	$ipn_verbose = "<p><b>Note:</b><br>To handle APN return requests, please set IPN Listener URL field to: <b>$ipn_url</b> ";
	$ipn_verbose .= "on your pesapal account settings</p>";
	
	$output['admin_filtered_string'] = [
		'#markup' => $this->t($ipn_verbose),
	];

    $form['uc_pesapal_is_live'] = array(
        '#type' => 'radios',
        '#title' => $this->t('Use Live Pesapal Account?'),
        '#options' => $options,
        '#description' => $this->t('Use Live Pesapal Account?'),
        '#default_value' => isset($this->configuration['uc_pesapal_is_live']) ? $this->configuration['uc_pesapal_is_live'] : $options['0'],
        '#required' => TRUE,
        '#validated' => TRUE,
    );
    
    $form['uc_pesapal_consumer_key'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Consumer key'),
      '#description' => $this->t('Consumer key from pesapal. Register to get one'),
      '#default_value' => $this->configuration['uc_pesapal_consumer_key'],
      '#size' => 50,
      '#required' => TRUE,
      '#validated' => TRUE,
    );
    
    $form['uc_pesapal_consumer_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Consumer secret'),
      '#description' => $this->t('Consumer secret from pesapal. Register to get one'),
      '#default_value' => $this->configuration['uc_pesapal_consumer_secret'],
      '#size' => 50,
      '#required' => TRUE,
      '#validated' => TRUE,
    );
   
    return array_merge($output,$form);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $uc_pesapal_is_live = $form_state->getValue('settings')['uc_pesapal_is_live'];
    $uc_pesapal_consumer_key = $form_state->getValue('settings')['uc_pesapal_consumer_key'];
    $uc_pesapal_consumer_secret = $form_state->getValue('settings')['uc_pesapal_consumer_secret'];
	
    $this->configuration['uc_pesapal_is_live'] = $uc_pesapal_is_live;
    $this->configuration['uc_pesapal_consumer_key'] = $uc_pesapal_consumer_key;
    $this->configuration['uc_pesapal_consumer_secret'] = $uc_pesapal_consumer_secret;
    
    //1. $form_state->getFormObject()->getEntity();
    //2. $form_state->getValue('settings')
    //var_dump($uc_pesapal_is_live);
    //exit;
    
    # save the content to configuration 
    # table config
    $config = \Drupal::service('config.factory')->getEditable('uc_pesapal.settings');
    $config->set('uc_pesapal_is_live', $uc_pesapal_is_live)
            ->set('uc_pesapal_consumer_key', $uc_pesapal_consumer_key) 
            ->set('uc_pesapal_consumer_secret', $uc_pesapal_consumer_secret) 
            ->save();
//    $this->configuration['uc_pesapal_reference_code'] = $form_state->getValue('uc_pesapal_reference_code');
//    $this->configuration['uc_pesapal_desc'] = $form_state->getValue('uc_pesapal_desc');
//    $this->configuration['uc_pesapal_policy'] = $form_state->getValue('uc_pesapal_policy');
//    $this->configuration['uc_pesapal_completed_msg'] = $form_state->getValue('uc_pesapal_completed_msg');
//    $this->configuration['uc_pesapal_pending_msg'] = $form_state->getValue('uc_pesapal_pending_msg');
//    $this->configuration['uc_pesapal_failed_msg'] = $form_state->getValue('uc_pesapal_failed_msg');
//    $this->configuration['uc_pesapal_invalid_msg'] = $form_state->getValue('uc_pesapal_invalid_msg');
  }


}
