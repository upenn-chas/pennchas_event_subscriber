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
		if (empty($config)) {
			return;
		}
		$go_button = $config->get('go_button') ?? 'Go';
		$group_types = $config->get('group_types');
		$link_text = $config->get('link_text') ?? 'Discover all our houses';
		$link_url = $config->get('link_url') ?? '#';

		$group_storage = \Drupal::entityTypeManager()->getStorage('group');
		$group_ids = $group_storage->getQuery()->accessCheck(false)->execute();
		$groups = Group::loadMultiple($group_ids);
		// Sort groups alphabetically by title
		usort($groups, function ($a, $b) {
			// Get titles of the groups
			$title_a = $a->label(); // 'label' is the function to get the group title.
			$title_b = $b->label();

			// Compare titles alphabetically (case-insensitive)
			return strcasecmp($title_a, $title_b);
		});

		$options = [];
		$options_html = '';

		foreach ($groups as $group) {
			$options[$this->getGroupUrlAlias($group)] = $group->label();
			$options_html .= '<li class="option"><span class="option-text" data-group-url="' . $this->getGroupUrlAlias($group) . '">' . $group->label() . '</span></li>';
		}
		$list_items = array(
			'lastname',
			'email',
			'phone'
		);

		$prefix = '<h2>Find Your College House</h2>';

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
						'#attributes' => [
							'class' => ['hidden'],
						],
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
					'#url' => '/all-college-houses',
					'#title' => $this->t($link_text),
					'#attributes' => [
						'class' => ['discover_all_houses'],
					],
				]
			],
			'#attributes' => [
				'class' => ['find_your_house form-group'],
			],
			'#markup' => '<div class="select-menu"><div class="select-btn"><span class="sBtn-text" role="button" tabindex="0">Select your House Name</span><div class="select-arrow" aria-label="Select dropdown" role="button" tabindex="0" aria-pressed="false"></div><ul class="options">' . $options_html . '</ul></div></div>',
			// '#markup' => implode('</span></li><li class="option"><span class="option-text">', $options),
			// '#suffix' => '</span></li></ul></div><div class="form-action"><button type="submit">Go</button></div>'
			// '#markup' => $prefix . $content
		];

		$build['#attached']['library'][] = 'find_your_house/find_your_house';
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
