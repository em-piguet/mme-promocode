/**
 * Validation du code promo via API manageme
 * Author : Wonderweb
 */

(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    // Vérifier si ajaxurl est déjà défini, sinon utiliser l'URL par défaut
    var ajaxurl = manageme_promo.ajax_url;
    document.querySelectorAll(".mm-promo-form").forEach(function (form) {
      form.addEventListener("submit", function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();

        var spinner = this.querySelector(".mm-promo-spinner");
        var id = this.dataset.society;
        var promoField = this.querySelector(".mm-promo-field");
        var promo = promoField.value;

        if (promo.length !== 0) {
          spinner.style.display = "block";

          // Utiliser FormData pour envoyer les données
          var formData = new FormData();
          formData.append("action", "validate_promo_code");
          formData.append("society_id", id);
          formData.append("promo_code", promo);

          fetch(ajaxurl, {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              message(data, "success");
            })
            .catch((error) => {
              console.error("Erreur :", error);
              message({ Message: "Une erreur est survenue" }, "error");
            })
            .finally(() => {
              spinner.style.display = "none";
            });
        } else {
          console.log("Le champ promo est vide");
        }
      });
    });

    function message(result, status) {
      //   console.log("Fonction message appelée avec :", result, status); // Debug
      var infoElement = document.querySelector(".mm-promo-info");

      if (status === "error") {
        infoElement.innerHTML = result.Message;
      }
      if (result.IsActive === false) {
        infoElement.innerHTML = result.Exceptions[0];
      }
      if (result.IsActive === true) {
        infoElement.innerHTML = "✅ " + manageme_promo.codeActivated;

        if (typeof appOpen === "function") {
          appOpen(result.Url);
        } else {
          infoElement.innerHTML +=
            ' - <a href="' +
            result.Url +
            '">' +
            manageme_promo.goToCart +
            "</a>";
        }
      }
    }
  });
})();
