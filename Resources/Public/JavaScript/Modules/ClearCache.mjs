/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

import { AjaxResponse } from '@typo3/core/ajax/ajax-response.js';
import Notification from '@typo3/backend/notification.js';
import Icons from '@typo3/backend/icons.js';
import RegularEvent from '@typo3/core/event/regular-event.js';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';

const Identifiers = {
    clearCache: '.deepl-clear-page-cache:not([disabled])',
    icon: '.t3js-icon',
}

/**
 * Module: @typo3/backend/clear-cache
 */
class ClearCache {
    constructor() {
        this.registerClickHandler();
    }

    static setDisabled(element, isDisabled) {
        element.disabled = isDisabled;
        element.classList.toggle('disabled', isDisabled);
    }

    /**
     * Send an AJAX request to clear a page's cache
     *
     * @param {number} pageId
     * @return Promise<AjaxResponse>
     */
    static sendClearCacheRequest(pageId) {
        const request = new AjaxRequest(TYPO3.settings.ajaxUrls.deepl_clear_page_cache).withQueryArguments({ id: pageId }).get({ cache: 'no-cache' });
        request.then(async (response) => {
            const data = await response.resolve();
            if (data.success === true) {
                Notification.success(data.title, data.message, 1);
            } else {
                Notification.error(data.title, data.message, 1);
            }
        }, () => {
            Notification.error(
                'Clearing deepl page cache went wrong on the server side.',
            );
        });

        return request;
    }

    registerClickHandler() {
        const trigger = document.querySelector(`${Identifiers.clearCache}:not([disabled])`);
        if (trigger !== null) {
            new RegularEvent('click', (e) => {
                e.preventDefault();

                // The action trigger behaves like a button
                const me = e.currentTarget;
                const id = parseInt(me.dataset.id, 10);
                ClearCache.setDisabled(me, true);

                Icons.getIcon('spinner-circle', Icons.sizes.small, null, 'disabled').then((icon) => {
                    me.querySelector(Identifiers.icon).outerHTML = icon;
                });

                ClearCache.sendClearCacheRequest(id).finally(() => {
                    Icons.getIcon('actions-system-cache-clear', Icons.sizes.small).then((icon) => {
                        me.querySelector(Identifiers.icon).outerHTML = icon;
                    });
                    ClearCache.setDisabled(me, false);
                });
            }).bindTo(trigger);
        }
    }
}

export default new ClearCache();
