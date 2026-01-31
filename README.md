# Módulo CategoryProductPos en Magento2.

Este es un proyecto trabajado por el **Ing. Oscar Rolando Gamboa Acho** con el fin de aportar a otros developers en el desarrollo de nuevos módulos y/o sistemas completos para sus proyectos.

Si existen dudas, observaciones, errores encontrados, ir a Issues y hacer el reporte sobre algun detalle para corregir y poder mejorar el módulo que se encuentra en este repositorio. :nerd_face:

---

## CONTENIDO
* [Sobre Magento](#sobre-magento)
* [Proyecto](#proyecto)
  * [Versiones](#versiones)
  * [Instalar](#instalar)
  * [Funcionamiento](#funcionamiento)
    * [CLI Command](#cli-command)
    * [GraphQl](#graphql) 
* [Actualizaciones](#actualizaciones)
* [Dudas o Preguntas](#dudas-o-preguntas)
---

## Sobre Magento
Es una plataforma de comercio electrónico open source o código liberado mediante el que se pueden gestionar todo tipo de e-Commerce, tambien existe el enterprise, pero este ultimo tiene un costo y mas caracteristicas.

Magento permite construir una tienda online a medida. Es una herramienta que cuenta con determinadas funcionalidades y de código abierto.

En un principio, surgió en 2007, y se lanzó al mercado como solución de comercio electrónico. Ahora cuenta con más funcionalidades, y varias versiones en función de las necesidades o el volumen de cada comercio online.
Puede descargarla en la página oficial de [Adobe Commerce](https://business.adobe.com/la/products/magento/open-source.html) o descargarlo por una version en específica en [Github](https://github.com/magento/magento2).

---

## Proyecto

Este proyecto consiste en un módulo para ordenar un producto de una categoría en específico por su posición para la PLP (Product List Page), esto con el fin de ordenar de acuerdo al gusto del cliente y/o equipo técnico y realizar el cambio de manera mas sencilla y rápida.
La tarea de cambiar posición de un producto dentro de una categoría puede realizarse de dos formas ya sea por medio del uso de un CLI Command o por medio de GraphQl y así ver el cambio dentro del Product List Page (PLP).

---

## Versiones
* Magento 2.4.6 (Open Source).
* PHP 8.1, 8.2, 8.3, 8.4.

---

## Instalar
La instalación del proyecto es muy sencillo, lo unico que puedes hacer es clonar este proyecto dentro del app/code en el framework, crea el directorio Devlat (en el mismo app/code) y luego clonas el directorio.
Luego para instalar el proyecto dentro de magento framework realiza los siguientes pasos:
* ```bin/magento module:status``` (verifica que tu módulo se encuentra dentro de los que no estan instalados que por default esta inactivo o deshabilitado).
* Ejecuta el siguiente comando para habilitar el módulo: ```bin/magento module:enable Devlat_CategoryProductPos```.
* Ejecuta ```bin/magento setup:upgrade``` para proceder con la instalacion del módulo.

---

## Funcionamiento
Tenemos dos formas para poder trabajar con este módulo para el salto de posiciones del producto dentro de una categoría.

### CLI Command
Para esto debes tomar en cuenta lo siguiente:
* Debes declarar para que categoria hay que aplicar el cambio de posición de un producto, inserta el nombre de la categoria.
* Declara un producto para aplicar el cambio de posición, solo se toma el sku.
* Declara por cuantas posiciones debe recorrer el producto, aca lo consideramos como salto (jump), tiene que ser un 
numero positivo o negativo (no cero).
Una vez teniendo conocimiento de esto, en este módulo tenemos un CLI Command Custom, que requerirá de estos datos que hemos mencionado anteriormente, vea los siguientes ejemplos:

  - `bin/magento devlat:category:position -c "Watches" --sku="24-WG02" --jump="-15"`
  - `bin/magento devlat:category:position -c "Watches" --sku="24-WG02" --jump="1"`
  - `bin/magento devlat:category:position --category="Watches" --sku="24-WG02" --jump="1"`


### GraphQl
Si deseas cambiar la posición de un producto, puedes hacerlo por medio de request GraphQl, ya que se desarrolló una mutación para poder cambiar la posición de un producto determinado en una categoría, toma en cuenta de 
que el request que se hizo prueba es la siguiente:

```
mutation setProductPos($category: String!, $sku: String!, $jump: Int!) {
    productPosition(input: {category: $category, sku: $sku, jump: $jump}) {
        product {
            ... on ProductPositioned {
                category
                sku
                newPosition   
            }
        }
    }
}
```
Preste atención de que tenemos variables **category**, **skus** y **jump**. Estos son variables GraphQl que son nuestros datos de entrada en este caso:
```
{
    "category": "Watches",
    "sku": "24-WG02",
    "jump": "2"
}
```
Dando como salida el siguiente resultado bajo el formato que se dio en schema:
```
{
    "data": {
        "productPosition": {
            "product": {
                "category": "Watches",
                "sku": "24-WG02",
                "newPosition": 5
            }
        }
    }
}
```
Dentro de ProductPosition tenemos el nodo **product** y tenemos los datos de category, que es el nombre de la categoria en el cual el producto esta, 
luego se tiene el **sku** del producto, **newPosition** es el dato que contiene la posición que se actualizó.

---

## Actualizaciones
Para la versión 1.2.0 se realizó las siguientes mejoras:
* Refactorización del codigo en CLI y GraphQl.
* Ordenamiento de datos para la actualización de posiciones de productos en una categoría.
* Dentro de la simplificación del código, se quito un parametro innecesario "mode" dentro de el CLI Command y GraphQl.

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



