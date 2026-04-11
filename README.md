# Magento2 module: CategoryProductPos.

This is a project developed by **Oscar Rolando Gamboa Acho** with the goal of contributing to other developers in the creation of new modules and/or complete systems for their projects.

If you have any doubts, observations, or errors found, go to Issues and report any details to correct and improve the module available in this repository. :nerd_face:

---

## CONTENTS
* [About Magento](#about-magento)
* [Project](#project)
    * [Versions](#versions)
    * [Install](#install)
    * [How It Works](#how-it-works)
        * [CLI Command](#cli-command)
        * [GraphQl](#graphql)
        * [Admin](#admin)
* [Updates](#updates)
* [Questions or Inquiries](#questions-or-inquiries)
---

## About Magento
It is an open source e-commerce platform through which all types of e-Commerce can be managed. An enterprise version also exists, but the latter has a cost and more features.

Magento allows you to build a custom online store. It is a tool that comes with certain functionalities and open source code.

It originally emerged in 2007 and was launched as an e-commerce solution. It now has more functionalities and several versions depending on the needs or volume of each online store.
You can download it from the official [Adobe Commerce](https://business.adobe.com/la/products/magento/open-source.html) page or download a specific version on [Github](https://github.com/magento/magento2).

---

## Project

This project consists of a module to sort a product within a specific category by its position for
the PLP (Product List Page), with the goal of ordering according to the client's and/or technical team's preference
and making the change more easily and quickly, either through
a command, a GraphQL request, or graphically from the admin.

---

## Versions
* Magento 2.4.6 (Open Source).
* PHP 8.1, 8.2, 8.3, 8.4.

---

## Install
Installing the project is very simple. All you need to do is clone this project inside the app/code directory in the framework, create the Devlat directory (also inside app/code), and then clone the directory there.
Then, to install the project within the Magento framework, follow these steps:
* ```bin/magento module:status``` (verify that your module appears in the list of uninstalled modules, which are disabled by default).
* Run the following command to enable the module: ```bin/magento module:enable Devlat_CategoryProductPos```.
* Run ```bin/magento setup:upgrade``` to proceed with the module installation.

---

## How It Works
We have three ways to work with this module for position jumping and/or
reorganizing the product order for the PLP (Product List Page).

### CLI Command
For this, keep the following in mind:
* COMMAND #1:
    * You must declare which category the product position change should be applied to by inserting the category name.
    * Declare a product to apply the position change to — only the SKU is required.
    * Declare how many positions the product should move; this is referred to as a jump, and it must be a
      positive or negative number (not zero).
      With this in mind, this module includes a Custom CLI Command that will require the data mentioned above. See the following examples:

        - `bin/magento devlat:category:position -c "Watches" --sku="24-WG02" --jump="-15"`
        - `bin/magento devlat:category:position -c "Watches" --sku="24-WG02" --jump="1"`
        - `bin/magento devlat:category:position --category="Watches" --sku="24-WG02" --jump="1"`
* COMMAND #2:
    * You must declare the category name and the type that will be the basis for
      reordering the products within a category.
    * Both the category name and the type are REQUIRED.
    * Usage is straightforward. Here are some examples:
        - `bin/magento devlat:category:reorganize -c "Watches" --type="sku"`
        - `bin/magento devlat:category:reorganize --category="Watches" --type="id"`


### GraphQl
If you want to change a product's position, you can do so through a GraphQL request, as a mutation was developed to change the position of a specific product within a category. The request used for testing is the following:

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
Note that we have the variables **category**, **sku**, and **jump**. These are GraphQL variables that serve as our input data:
```
{
    "category": "Watches",
    "sku": "24-WG02",
    "jump": "2"
}
```
Producing the following output in the format defined by the schema:
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

Inside ProductPosition we have the **product** node with the following fields: **category**, which is the name of the category the product belongs to; **sku**, the product's SKU; and **newPosition**, which holds the updated position value.

### Admin
Navigate to: **Admin > Catalog > Categories**, where you will find a new section
called *Reorganize Products*.

When you open that section, you will see a list of products. From there, you can drag and drop a product to reposition it before or after another product. Simply drag it to the desired position to change the order for the Product List Page of that category, then click the **Update Products Order** button.

---

## Updates
For version 1.3.2, the following improvements were made:
* Fixed the problem when the user adds or removes a product in the admin, 
in order to avoid conflicts in products positions.

---

## Questions or Inquiries
If you have any questions or need help with the module, feel free to reach out through my social media — I'll get back to you as soon as possible :sunglasses::

  <a href="https://www.linkedin.com/in/oscarrolandogamboa/">
      <img align="left" alt="Oscar Rolando Gamboa Acho | Linkedin" width="30px" src="https://github.com/SatYu26/SatYu26/blob/master/Assets/Linkedin.svg" />
  </a> &nbsp;&nbsp;
  <a href="https://x.com/DevLatBo">
    <img align="left" alt="Oscar Rolando Gamboa Acho | Twitter" width="32px" src="https://user-images.githubusercontent.com/8138585/256154469-3d935a39-9abc-4ba6-94d4-b8e163756c27.svg" />
  </a> &nbsp;&nbsp;
  <a href="https://youtube.com/DevLatBo">
    <img align="left" alt="Oscar Rolando Gamboa Acho | Youtube" width="30px" src="https://user-images.githubusercontent.com/47686437/168548113-b3cd4206-3281-445b-b7c6-bc0a3251293d.png" />
  </a> &nbsp;&nbsp;
