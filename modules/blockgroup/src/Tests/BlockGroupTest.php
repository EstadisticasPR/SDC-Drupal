<?php

/**
 * @file
 * Contains \Drupal\block_class\BlockClassTest.
 */

namespace Drupal\blockgroup\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the access of block groups and CRUD.
 *
 * @group blockgroup
 */
class BlockGroupTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('block', 'blockgroup');

  /**
   * A function which calls all the tests function so it works faster.
   */
  protected function testBlockGroup() {
    // Its used like this because it runs faster.
    $this->blockGroupUserAccess();
    $this->blockGroup();
  }

  /**
   * Creates a block group and verifies its consistency.
   */
  protected function blockGroupUserAccess() {

    // Anonymous users can't access the page.
    $this->drupalGet('admin/structure/block_group_content');
    $this->assertResponse(403, 'Access is denied on the administration page for anonymous users.');

    // Authenticated users without the "administer blocks" permission can't
    // access the page.
    $web_user1 = $this->drupalCreateUser();
    $this->drupalLogin($web_user1);
    $this->drupalGet('admin/structure/block_group_content');
    $this->assertResponse(403, 'Access is denied on the administration page for users without "administer blocks" permission.');

    // Authenticated users with "administer blocks" permission can access the
    // page.
    $web_user2 = $this->drupalCreateUser(['administer blockgroups']);
    $this->drupalLogin($web_user2);
    $this->drupalGet('admin/structure/block_group_content');
    $this->assertResponse(200, 'The block group page can be accessed at admin/structure/block_group_content.');
  }

  /**
   * Tests creating a block group programmatically.
   */
  protected function blockGroup() {

    $web_user = $this->drupalCreateUser(['administer blockgroups']);
    $this->drupalLogin($web_user);

    // Verify that we can manage entities through the user interface.
    $blockgroup_label = 'group_test';
    $blockgroup_machine_name = 'group_test';
    $this->drupalPostForm(
      '/admin/structure/block_group_content/add',
      [
        'label' => $blockgroup_label,
        'id' => $blockgroup_machine_name,
      ],
      t('Save')
    );
    // Check if the created blockgroup is in the list.
    $this->drupalGet('/admin/structure/block_group_content');
    $this->assertText($blockgroup_machine_name, 'The created blockgroup exists in the list');
    // Verify that "Group test" blockgroup is editable.
    $this->drupalGet('/admin/structure/block_group_content/' . $blockgroup_machine_name);
    $this->assertField('label');
    // Verify that "Group test" blockgroup can be deleted.
    $this->drupalPostForm(
      '/admin/structure/block_group_content/' . $blockgroup_machine_name . '/delete',
      [],
      t('Delete')
    );
  }

}
