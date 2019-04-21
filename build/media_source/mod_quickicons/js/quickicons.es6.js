/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document, Joomla) => {
  'use strict';

  const init = () => {
    // Cleanup
    document.removeEventListener('DOMContentLoaded', init);

    // Get the elements
    const elements = [].slice.call(document.querySelectorAll('.quickicon-counter'));

    if (elements.length) {
      elements.forEach((element) => {
        const iconurl = element.getAttribute('data-url');

        if (iconurl && Joomla && Joomla.request && typeof Joomla.request === 'function') {
          Joomla.request({
            url: iconurl,
            method: 'POST',
            onSuccess: (resp) => {
              let response;
              try {
                response = JSON.parse(resp);
              } catch (error) {
                throw new Error('Failed to parse JSON');
              }
		if (response.data) {
                const elem = document.createElement('span');
                elem.innerHTML = response.data;

                element.parentNode.replaceChild(elem, element);
		}
            }
          });
        }
      });
    }
  };
  
  document.addEventListener('DOMContentLoaded', init);
})(document, Joomla);
