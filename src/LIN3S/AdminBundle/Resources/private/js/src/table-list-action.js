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

import {EventPublisher, DOMReadyEventSubscriber} from 'lin3s-event-bus';

import $ from 'jquery';

function onReady() {
  var $removeAction = $('.table__action--remove, .table__action--eliminar');

  if ($removeAction.length === 0) {
    return;
  }

  $removeAction.click(function () {
    return !!confirm('Are you sure that you want to remove?');
  });
}

const init = () => {
  EventPublisher.subscribe(
    new DOMReadyEventSubscriber(
      onReady
    )
  );
};

export default init();