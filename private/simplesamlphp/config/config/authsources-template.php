<?php
// rename to authsources.php
// set $primary_domain to primary live domain
$primary_domain = 'global.upenn.edu';
$entity_id = 'https://' . $primary_domain . '/simplesaml/module.php/saml/sp/metadata.php/default-sp';
$sp_name = 'default-sp';

$config = array(

	// This is a authentication source which handles admin authentication.
	'admin' => array(
		// The default is to use core:AdminPassword, but it can be replaced with
		// any authentication source.

		'core:AdminPassword',
	),
	// An authentication source which can authenticate against both SAML 2.0
	// and Shibboleth 1.3 IdPs.
	'default-sp' => array(
    'saml:SP',
		'privatekey' => $sp_name . '.pem',
		'certificate' => $sp_name . '.crt',
		'entityID' => $entity_id,
		'idp' => 'https://idp.pennkey.upenn.edu/idp/shibboleth',
		'discoURL' => NULL,
		'authproc' => array(
			50 => array( // map attributes to names rather than numeric ids
				'class' => 'core:AttributeMap', 'oid2name'
			),
			60 => array( // replace colons in group names
				'class'   => 'core:AttributeAlter',
				'subject' => 'eduPersonEntitlement',
				'pattern' => '/:/',
				'replacement' => '.'
			),
		),
	),
);
