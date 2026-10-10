<?php
/** Run with: php contrib/tests/user-edit-attribute-targets.php */
require_once __DIR__ . '/../../app/operators/library/attributes.php';
require_once __DIR__ . '/../../app/operators/library/user_edit.php';

function expect_target($field, $target, $id = 0) {
    $result = dalo_user_edit_attributes(array('dictValues2' => $field), array(), array('==', ':='));
    $expected = array(array($id, 'Calling-Station-Id', 'test-value', '==', $target));
    if ($result !== $expected) {
        throw new RuntimeException('Unexpected attribute normalization');
    }
}

function expect_rejected($field) {
    try {
        dalo_user_edit_attributes(array('dictValues2' => $field), array(), array('==', ':='));
    } catch (InvalidArgumentException $error) {
        return;
    }
    throw new RuntimeException('Invalid attribute was accepted');
}

try {
    // Match the actual four-slot controls emitted by dynamic_attributes.js.
    foreach (array('check' => 'radcheck', 'reply' => 'radreply',
                   'radcheck' => 'radcheck', 'radreply' => 'radreply') as $input => $target) {
        expect_target(array('Calling-Station-Id', 'test-value', '==', $input), $target);
        expect_target(array('42__Calling-Station-Id', 'test-value', '==', $input), $target, 42);
    }
    expect_target(array(' Calling-Station-Id ', ' test-value ', ' == ', ' check '), 'radcheck');
    foreach (array('', 'nas', 'operators', 'radacct', 'CHECK', 'radcheck; DROP TABLE radcheck', 'check_extra') as $target) {
        expect_rejected(array('Calling-Station-Id', 'test-value', '==', $target));
    }
    expect_rejected(array('Calling-Station-Id', 'test-value', 'invalid', 'check'));
    expect_rejected(array('Calling-Station-Id', '', '==', 'check'));
    expect_rejected(array('Calling-Station-Id', 'test-value', '==', array('check')));
    expect_rejected(array('Calling-Station-Id', 'test-value', '=='));
    // IDs are scoped to a table, so same-ID check/reply controls must stay separate.
    $paired = dalo_user_edit_attributes(array(
        'editValues_radcheck_42' => array('42__Calling-Station-Id', 'test-value', '==', 'radcheck'),
        'editValues_radreply_42' => array('42__Reply-Message', 'reply-value', ':=', 'radreply'),
    ), array(), array('==', ':='));
    if ($paired !== array(array(42, 'Calling-Station-Id', 'test-value', '==', 'radcheck'),
                          array(42, 'Reply-Message', 'reply-value', ':=', 'radreply'))) {
        throw new RuntimeException('Same-ID check/reply controls were not preserved');
    }
    echo "PASS: dynamic targets, existing targets, paired IDs and invalid controls\n";
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . "\n");
    exit(1);
}
