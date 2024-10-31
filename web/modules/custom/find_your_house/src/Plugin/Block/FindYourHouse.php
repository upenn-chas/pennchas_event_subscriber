<?php

namespace Drupal\find_your_house\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
// use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a 'Find Your House' Block.
 *
 * @Block(
 *   id = "find_your_house",
 *   admin_label = @Translation("Find Your House"),
 *   category = @Translation("Custom")
 * )
 */
class FindYourHouse extends BlockBase
{
	/**
	 * {@inheritdoc}
	 */
	public function build()
	{
		$config = \Drupal::config('find_your_house.settings');
		if(empty($config)) {
			return;
		}
		$go_button = $config->get('go_button') ?? 'Go';
		$group_types = $config->get('group_types');
		$link_text = $config->get('link_text') ?? 'Discover all our houses';
		$link_url = $config->get('link_url')?? '#';

		// $group_query = \Drupal::entityQuery('group')
		// ->condition('type', $group_types ,'IN')
		// ->accessCheck(true)
		// ->execute();
    $group_storage = \Drupal::entityTypeManager()->getStorage('group');
    $group_ids = $group_storage->getQuery()->accessCheck(false)->execute();
    $groups = Group::loadMultiple($group_ids);
		$options = [];

		foreach ($groups as $group) {
			$options[$this->getGroupUrlAlias($group)] = $group->label();
		}

		$build = [
			"#type" => "container",
			'form_wrapper' => [
				'#type' => 'container',
				'#attributes' => [
					'class' => ['form_wrapper'],
				],
				"form" => [
					'#type' => 'form',
					'#id' => 'customer_find_your_house',
					'#attributes' => [
						'onsubmit' => 'window.location.href=document.getElementById(\'customer_find_your_house_group\').value; return false;',
					],
					'group_select' => [
						'#id' => 'customer_find_your_house_group',
						'#type' => 'select',
						'#title' => '',
						'#empty_option' => $this->t('Select Your House Name'),
						'#options' => $options,
					],
					'submit' => [
						'#type' => 'submit',
						'#value' => $this->t($go_button),
						'#attributes' => [
							'class' => ['group-redirect-button'],
						],
					],
				],
			],
			'link_wrapper' => [
				'#type' => 'container',
				'#attributes' => [
					'class' => ['link_wrapper'],
				],
				'link' => [
					'#type' => 'link',
					'#url' => '',
					'#title' => $this->t($link_text),
					'#attributes' => [
						'class' => ['discover_all_houses'],
					],
				]
			],
			'#attributes' => [
				'class' => ['find_your_house'],
			]
		];

		return $build;
	}

	/**
	 * Helper function to get the URL alias for a group.
	 *
	 * @param \Drupal\group\Entity\Group $group
	 *   The group entity.
	 *
	 * @return string
	 *   The URL alias of the group.
	 */
	protected function getGroupUrlAlias(Group $group)
	{
		$group_path = '/group/' . $group->id();
		$alias_manager = \Drupal::service('path_alias.manager');
		return $alias_manager->getAliasByPath($group_path);
	}
}
