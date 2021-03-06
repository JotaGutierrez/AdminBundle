/*
 * This file is part of the Admin Bundle.
 *
 * Copyright (c) 2015-2016 LIN3S <info@lin3s.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Beñat Espiña <benatespina@gmail.com>
 * @author Gorka Laucirica <gorka.lauzirika@gmail.com>
 */

'use strict';

import {onDomReady} from 'lin3s-event-bus';

import $ from 'jquery';

function onReady() {
  if ($('.filter').length === 0) {
    return;
  }

  $('.filter__filter-by').change(function () {
    var $options = $('.filter__options');

    $options.children().addClass('filter__option--hidden').attr('name', '');
    $options
      .find('[data-filter-field="' + $(this).val() + '"]')
      .removeClass('filter__option--hidden').attr('name', 'filter').focus();
  });
}

onDomReady(onReady);
