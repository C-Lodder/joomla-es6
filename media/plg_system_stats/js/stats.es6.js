/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @since      3.5.0
 */

Joomla = window.Joomla || {};

((Joomla, document) => {
  'use strict';

  const data = {
    'option' : 'com_ajax',
    'group'  : 'system',
    'plugin' : 'renderStatsMessage',
    'format' : 'raw'
  };

  Joomla.initStatsEvents = () => {
    const messageContainer = document.getElementById('system-message-container');
    const joomlaAlert = messageContainer.querySelector('.js-pstats-alert');
    const detailsContainer = messageContainer.querySelector('.js-pstats-data-details');
    const details = messageContainer.querySelector('.js-pstats-btn-details');
    const always = messageContainer.querySelector('.js-pstats-btn-allow-always');
    const once = messageContainer.querySelector('.js-pstats-btn-allow-once');
    const never = messageContainer.querySelector('.js-pstats-btn-allow-never');

    // Show details about the information being sent
    document.addEventListener('click', event => {
      if (event.target.classList.contains('js-pstats-btn-details')) {
        event.preventDefault();
        detailsContainer.classList.toggle('d-none');
      }
    });

    // Always allow
    document.addEventListener('click', event => {
      if (event.target.classList.contains('js-pstats-btn-allow-always')) {
        event.preventDefault();

        // Remove message
        joomlaAlert.close();

        // Set data
        data.plugin = 'sendAlways';

        Joomla.getJson(data);
      }
    });

    // Allow once
    document.addEventListener('click', event => {
      if (event.target.classList.contains('js-pstats-btn-allow-once')) {
        event.preventDefault();

        // Remove message
        joomlaAlert.close();

        // Set data
        data.plugin = 'sendOnce';

        Joomla.getJson(data);
      }
    });

    // Never allow
    document.addEventListener('click', event => {
      if (event.target.classList.contains('js-pstats-btn-allow-never')) {
        event.preventDefault();

        // Remove message
        joomlaAlert.close();

        // Set data
        data.plugin = 'sendNever';

        Joomla.getJson(data);
      }
    });
  }

  Joomla.getJson = data => {
    const messageContainer = document.getElementById('system-message-container');
    Joomla.request({
      url: 'index.php?option=' + data.option + '&group=' + data.group + '&plugin=' + data.plugin + '&format=' + data.format,
      headers: {
        'Content-Type': 'application/json'
      },
      onSuccess: (response, xhr) => {
        try {
          response = JSON.parse(response);
        } catch (e) {
          throw new Error(e);
        }

        if (response && response.html) {
          messageContainer.innerHTML = response.html;
          messageContainer.querySelector('.js-pstats-alert').style.display = 'block';

          Joomla.initStatsEvents();
        }
      },
      onError: (xhr) => {
        Joomla.renderMessages({
          error: [xhr.response]
        });
      }
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    data.plugin = 'sendStats';
    Joomla.getJson(data);
  });

})(Joomla, document);