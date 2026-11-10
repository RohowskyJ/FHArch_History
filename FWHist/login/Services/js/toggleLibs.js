$(function() {
  // Convert existing checkbox labels into clickable strings (keeps checkbox for fallback)
  $('input[type="checkbox"][id^="toggleGroup"]').each(function() {
    var id = $(this).attr('id');
    var groupKey = id.replace('toggleGroup','');
    var $label = $(this).closest('label');
    if ($label.length) {
      // Extract label text (excluding input)
      var labelText = $label.clone().children().remove().end().text().trim();
      if (labelText.length) {
        // Insert clickable string after the checkbox
        var $link = $('<a href="#" class="toggle-string" data-toggle-group="' + groupKey + '">' + labelText + '</a>');
        $(this).after(' ');
        $(this).after($link);
        // Optionally remove original text to avoid duplication
        $label.contents().filter(function() { return this.nodeType === 3; }).remove();
      }
    }
  });
  // Einzelblock-Toggle wie zuvor
  $('input[type="checkbox"][id^="toggleBlock"]').each(function() {
    var blockId = $(this).attr('id').replace('toggleBlock', 'block');
	console.log('BlockId ',blockId);
    $('#' + blockId).toggleClass('visible', this.checked);

    $(this).on('change', function() {
      $('#' + blockId).toggleClass('visible', this.checked);
    });
  });

  // Gruppen-Toggle
  $('input[type="checkbox"][id^="toggleGroup"]').each(function() {
    var groupName = $(this).attr('id').replace('toggleGroup', 'group');
	console.log('groupName ',groupName);
    var $groupBlocks = $('.toggle-group[data-group="' + groupName + '"] .toggle-block');
	console.log('groupBlocks ',$groupBlocks);
    $groupBlocks.toggleClass('visible', this.checked);

    $(this).on('change', function() {
      $groupBlocks.toggleClass('visible', this.checked);
    });
  });

  // Click-Trigger: Elemente mit data-toggle-group="groupX" oder data-toggle-group="X"
  $(document).on('click', '[data-toggle-group]', function(e) {
    e.preventDefault();
    var tgt = $(this).data('toggle-group');
	console.log('ToggleGroup ', tgt);
    if (!tgt) return;
    // Normalize: allow 'group1' or '1'
    var groupKey = (String(tgt).indexOf('group') === 0) ? String(tgt).replace('group','') : String(tgt);
    var fullGroup = 'group' + groupKey;
    var $blocks = $('.toggle-group[data-group="' + fullGroup + '"] .toggle-block');
    if ($blocks.length === 0) return;
    var shouldShow = !$blocks.first().hasClass('visible');
    $blocks.toggleClass('visible', shouldShow);
    // Sync checkbox if present
    var $cb = $('#toggleGroup' + groupKey);
    if ($cb.length) {
      $cb.prop('checked', shouldShow);
    }
  });

  // Radio Button Toggle: Switch between two groups
  // Example: <input type="radio" name="upload_method_1" value="library" data-toggle-group-a="lib1" data-toggle-group-b="upload1">
  $(document).on('change', 'input[type="radio"][data-toggle-group-a]', function() {
    if (!this.checked) return;
    var name = $(this).attr('name');
    var groupA = $(this).data('toggle-group-a');
    var groupB = $(this).data('toggle-group-b');
    var idx = $(this).data('toggle-index');
    if (!groupA || !groupB) return;
    // Normalize groups
    var fullGroupA = (String(groupA).indexOf('group') === 0) ? String(groupA) : 'group' + String(groupA);
    var fullGroupB = (String(groupB).indexOf('group') === 0) ? String(groupB) : 'group' + String(groupB);
    var $blocksA = $('.toggle-group[data-group="' + fullGroupA + '"] .toggle-block');
    var $blocksB = $('.toggle-group[data-group="' + fullGroupB + '"] .toggle-block');
    // Show A, hide B
    $blocksA.addClass('visible');
    $blocksB.removeClass('visible');
    // If user selected library, trigger startAjax if available
    if (typeof idx !== 'undefined' && String(groupA).indexOf('lib') !== -1) {
      if (window.startAjax) {
        try { window.startAjax(idx); } catch(e) { console.error('startAjax error', e); }
      } else if (window.startajax) { // some scripts use lowercase
        try { window.startajax(idx); } catch(e) { console.error('startajax error', e); }
      }
    }
  });

  // Initialize radio groups on load
  $('input[type="radio"][data-toggle-group-a]').each(function() {
    var groupA = $(this).data('toggle-group-a');
    var groupB = $(this).data('toggle-group-b');
    var idx = $(this).data('toggle-index');
	console.log('Index ', idx);
    if (!groupA || !groupB) return;
    var fullGroupA = (String(groupA).indexOf('group') === 0) ? String(groupA) : 'group' + String(groupA);
    var fullGroupB = (String(groupB).indexOf('group') === 0) ? String(groupB) : 'group' + String(groupB);
    var $blocksA = $('.toggle-group[data-group="' + fullGroupA + '"] .toggle-block');
    var $blocksB = $('.toggle-group[data-group="' + fullGroupB + '"] .toggle-block');
    if ($(this).is(':checked')) {
      $blocksA.addClass('visible');
      $blocksB.removeClass('visible');
    } else {
      $blocksA.removeClass('visible');
      $blocksB.addClass('visible');
    }
  });
});