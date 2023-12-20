# Módulo CategoryProductPos en Magento2.

Este es un proyecto trabajado por el **Ing. Oscar Rolando Gamboa Acho** con el fin de aportar a otros developers en el desarrollo de sus proyectos por medio de la orientacion en base a lo trabajado y facilitar el desarrollo de nuevos módulos y/o sistemas completos.

Si existen dudas, observaciones, errores encontrados, ir a Issues y hacer el reporte de algun detalle para trabajarlo y poder mejorar el modulo que se encuentra trabajado en este repositorio. :nerd_face:

---

## CONTENIDO
* [Sobre Magento](#sobre-magento)
* [Proyecto](#proyecto)
  * [Versiones](#versiones)
  * [Instalar](#instalar)
  * [Funcionamiento](#funcionamiento)
* [Dudas o Preguntas](#dudas-o-preguntas)
---

## Sobre Magento
Es una plataforma de comercio electrónico open source o código liberado mediante el que se pueden gestionar todo tipo de e-Commerce, tambien existe el enterprise, pero este ultimo tiene un costo y mas caracteristicas.

Magento permite construir una tienda online a medida. Es una herramienta que cuenta con determinadas funcionalidades y de código abierto.

En un principio, surgió en 2007, y se lanzó al mercado como solución de comercio electrónico. Ahora cuenta con más funcionalidades, y varias versiones en función de las necesidades o el volumen de cada comercio online.
Puede descargarla en la página oficial de [Adobe Commerce](https://business.adobe.com/la/products/magento/open-source.html).

---

## Proyecto

Este proyecto consiste en un módulo para el framework Magento2 para ordenar un(os) producto(s) de una categoria en específico por su posicion para la PLP (Product List Page), esto con el fin de ordenar de acuerdo al gusto del cliente y/o equipo técnico pueda hacer cambios de manera mas sencilla y rápida.
Este módulo fue desarrollado creando un custom CLI Command para poder ejecutar la funcionalidad del que estamos apuntando que es cambio de posición de un producto dentro de una categoría para el Product List Page (PLP).

### Versiones
* Magento 2.4.4 (Open Source).
* Composer: 1.9.3.
* PHP 8.1.

---

### Instalar
La instalación del proyecto es muy sencillo, lo unico que puedes hacer es clonar este proyecto dentro del app/code en el framework, crea el directorio Devlat (en el mismo app/code) y luego clonas el directorio.
Luego para instalar el proyecto dentro del framework realiza los siguientes pasos:
* ```bin/magento module:status``` (verifica que tu módulo se encuentra dentro de los que no estan instalados que por default esta inactivo o deshabilitado).
* Ejcuta el siguiente comando para habilitar el módulo: ```bin/magento module:enable Devlat_RelatedProducts```.
* Ejecuta ```bin/magento setup:upgrade``` para proceder con la instalacion del módulo.

---

### Funcionamiento
Para esto debes tomar en cuenta lo siguiente que necesitamos:
* Debes declarar para que categoria hay que aplicar el cambio de posición de un producto, opta por el nombre del producto.
* Puedes declarar un producto o mas de un producto para aplicar el cambio de posición, solo se toma el(los) sku(s).
* Declara por cuantas posiciones debe recorrer el producto, si sera ASCENDENTE o DESCENTENTE.
* Es opcional el parametro mode, pero es en base al modo, ya que en base al anterior punto. Tomar encuenta solo palabras DESC, ASC.
Una vez teniendo conocimiento de esto, en este módulo tenemos un CLI Command Custom, que requerira de estos datos que hemos memcionado en los anteriores puntos, vea los siguientes ejemplos:

* `bin/magento devlat:category:position -c "Categoria Name" --skus "prod-1, prod-b, prod-C" -p 1 ASC`
* `bin/magento devlat:category:position --category "Categoria Name" --skus "prod-1, prod-b, prod-C" -p 1 DESC`
* `bin/magento devlat:category:position -c "Categoria Name" --skus "prod-1" --pos 1`

Para separar los skus usa solo comas.

---

## Dudas o Preguntas
Si tienes alguna duda o pregunta para poder ayudarte con el modulo, favor contactame por mis redes sociales, que te puedo responder a la brevedad posible :sunglasses::

  <a href="https://www.linkedin.com/in/oscarrolandogamboa/">
      <img align="left" alt="Oscar Rolando Gamboa Acho | Linkedin" width="30px" src="https://github.com/SatYu26/SatYu26/blob/master/Assets/Linkedin.svg" />
  </a> &nbsp;&nbsp;
  <a href="https://x.com/DevLatBo">
    <img align="left" alt="Oscar Rolando Gamboa Acho | Twitter" width="32px" src="https://user-images.githubusercontent.com/8138585/256154469-3d935a39-9abc-4ba6-94d4-b8e163756c27.svg" />
  </a> &nbsp;&nbsp;
  <a href="https://youtube.com/DevLatBo">
    <img align="left" alt="Oscar Rolando Gamboa Acho | Youtube" width="30px" src="https://user-images.githubusercontent.com/47686437/168548113-b3cd4206-3281-445b-b7c6-bc0a3251293d.png" />
  </a> &nbsp;&nbsp;



