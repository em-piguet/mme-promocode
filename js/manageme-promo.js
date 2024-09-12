/**
 * Validation du code promo via API manageme
 * Author : Wonderweb
 */

(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    console.log("manage-me promo loaded"); // Debug
    var base_url = "https://www.manage-me.pro/api/society/";
    document.querySelectorAll(".mm-promo-form").forEach(function (form) {
      form.addEventListener("submit", function (event) {
        event.preventDefault();
        event.stopImmediatePropagation();

        var spinner = this.querySelector(".mm-promo-spinner");
        var id = this.dataset.society;
        var promoField = this.querySelector(".mm-promo-field");
        var promo = promoField.value;

        if (promo.length !== 0) {
          var api_url = base_url + id + "/promocode/" + promo;
          console.log("URL de l'API appelée :", api_url); // Debug

          spinner.style.display = "block";

          fetch(api_url)
            .then((response) => {
              console.log("Statut de la réponse :", response.status); // Debug
              return response.json();
            })
            .then((data) => {
              console.log("Requête réussie"); // Debug
              console.log("Réponse complète :", data); // Debug
              message(data, "success");
            })
            .catch((error) => {
              console.error("Erreur :", error); // Debug
              message({ Message: "Une erreur est survenue" }, "error");
            })
            .finally(() => {
              spinner.style.display = "none";
            });
        } else {
          console.log("Le champ promo est vide"); // Debug
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
        infoElement.innerHTML =
          '<a href="https://www.manage-me.pro' +
          result.Url +
          '">Aller au panier</a>';
      }
    }
  });
})();
