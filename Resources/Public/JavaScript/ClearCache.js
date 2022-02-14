define(['jquery','TYPO3/CMS/Backend/Notification','TYPO3/CMS/Backend/Icons','TYPO3/CMS/Core/Event/RegularEvent','TYPO3/CMS/Core/Ajax/AjaxRequest'],
  function ($, Notification, Icons, RegularEvent, AjaxRequest) {

  function setDisabled(element, isDisabled) {
    element.disabled = isDisabled;
    element.classList.toggle('disabled', isDisabled);
  }

  function sendClearCacheRequest(pageId) {
    const request = new AjaxRequest(TYPO3.settings.ajaxUrls.deepl_clear_page_cache).withQueryArguments({id: pageId}).get({cache: 'no-cache'});
    request.then(function(response) {
      return response.resolve();
    }).then(function (data) {
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

  function registerClickHandler() {
    var trigger = document.querySelector('.deepl-clear-page-cache:not([disabled])');
    if (trigger !== null) {
      new RegularEvent('click', function (e) {
        e.preventDefault();

        // The action trigger behaves like a button
        const me = e.currentTarget;
        const id = parseInt(me.dataset.id, 10);
        setDisabled(me, true);

        Icons.getIcon('spinner-circle-dark', Icons.sizes.small, null, 'disabled').then(function (icon) {
          me.querySelector('.t3js-icon').outerHTML = icon;
        });

        sendClearCacheRequest(id).finally(() => {
          Icons.getIcon('actions-system-cache-clear', Icons.sizes.small).then(function (icon) {
            me.querySelector('.t3js-icon').outerHTML = icon;
          });
          setDisabled(me, false);
        })
      }).bindTo(trigger);
    }
  }

  registerClickHandler();
});

