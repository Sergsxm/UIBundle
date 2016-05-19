# Sergsxm UIBundle

It\`s bundle for Symfony2 framework, which provides functions for creating forms, table lists and tree (order) forms. This is useful when creating a site\`s backend.

**The bundle being developed, be careful when using in projects.**

Forms features:

- The fields are combined into groups
- Control the visibility of the groups, depending on fields conditions
- JavaScript validation
- Advanced templating with field\`s templates replacement
- Ajax file upload

Table lists features:

- Use Doctrine 
- Support for tabs
- Sort by all fields 
- Text search function 
- Ajax actions 
- Saving the sorting settings, search settings, current page etc. in session

Tree (order) forms features:

- Use Doctrine
- Easy tree or order setup by JavaScript
- Supports mouse and touchscreen

## 1. Installation

To install SergsxmUIBundle, run `composer require sergsxm/ui-bundle`.

Register the bundle in the app/AppKernel.php file:

```php
...
public function registerBundles()
{
    $bundles = array(
        ...
        new Sergsxm\UIBundle\SergsxmUIBundle(),
        ...
    );
...
```

Register the bundle in the app/config/routing.yml file:

```yml
...
sergsxm_ui:
    resource: "@SergsxmUIBundle/Resources/config/routing.yml"
    prefix:   /
...
```

Install bundle assets by command `php app/console assets:install`.

Now you can use the bundle.

If you will change layout template, remember to add CSS and JS files of this bundle and initialization section on it (and Bootstrap, jQuery, TinyMCE files of course):

```twig
<html>
    <head>
        ...
        ...
        <link rel="stylesheet" href="{{asset('bundles/sergsxmui/css/ui.css')}}" />
        <script src="{{asset('bundles/sergsxmui/js/ui.js')}}"></script>
        <script>
            sergsxmUIFunctions.initContext('{{app.request.locale}}', '{{path('sergsxm_ui_file_upload')}}');
        </script>    
    </head>
    ...
    ...
</html>
```

### 1.1. Third-party modules

For the operation of the bundle following modules needed:

[Bootstrap](http://getbootstrap.com/)

[jQuery](https://jquery.com/)

[TinyMCE](https://www.tinymce.com/) (optionaly, used for html input type in forms)

[Yandex.maps](https://tech.yandex.ru/maps/) (optionaly, used for address input type in forms)

## 2. Forms usage

### 2.1. Form creation

To create the form use the service:

```php
$form = $this->get('sergsxm.ui')->createForm($object);
```

$object is a object for properties mapping. Leave this parameter or set it to null if this functionality is not needed.

Now you have Form (Sergsxm\UIBundle\Form\Form) object. This object contains following methods:

```php
public function addField($type, $name, $configuration = array(), $mappingObject = self::MO_PARENT);
public function openGroup($name, $description = '', $condition = '');
public function closeGroup();
public function enableCaptcha($type, $configuration = array());
public function disableCaptcha();
public function getFormId();
public function setFormId($formId);
public function setReadOnly($readOnly);
public function bindRequest(Request $request = null);
public function clear();
public function getResult();
public function getView();
public function renderView($template = 'SergsxmUIBundle:Form:Form.html.twig', $parameters = array());
public function render($template = 'SergsxmUIBundle:Form:Form.html.twig', $parameters = array(), Response $response = null);
public function findInputByName($name);
public function getValue();
public function setValue($value);
public function fromAnnotations($tag = null, $mappingObject = self::MO_PARENT);
```

Assigning functions described in greater detail in the class file. 

Below is a code example of using form:

```php
$object = new \StdClass();
$object->text1 = 'foo';
$object->textarea1 = '';
$object->select1 = '';
$object->checkbox1 = '';
$object->password1 = '';
$object->timestamp1 = '';
$object->html1 = '';
$object->file1 = null;

$form = $this->get('sergsxm.ui')->createForm($object);
        
$form
    ->addField('text', 'text1')
    ->openGroup('test', 'Test group', '')
    ->addField('textarea', 'textarea1')
    ->addField('select', 'select1', array(
        'choices' => ['One', 'Two', 'Three'],
    ))
    ->openGroup('test1', 'Subgroup', 'select1 == 1')
    ->addField('checkbox', 'checkbox1')
    ->addField('password', 'password1')
    ->closeGroup()
    ->addField('timestamp', 'timestamp1')
    ->openGroup('test2', 'Subgroup 2')
    ->addField('html', 'html1')
    ->enableCaptcha('standart')
    ->addField('file', 'file1', array(
        'storeType' => \Sergsxm\UIBundle\FormInputTypes\File::ST_FILE,
        'storeFolder' => 'uploadfiles',
    ));
if ($form->bindRequest()) {
    echo 'Data saved!';
    var_dump($form->getValue());
    var_dump($object);
    $form->clear();
    die;
}
        
return $form->render('SergsxmUIBundle:Form:Form.html.twig', array('title' => 'New form', 'backUrl' => '/123'));
```

### 2.2. Object\`s properties mapping

If you create a form specified object for properties mapping, the form values are automatically transferred to the object properties with the same name. 
Make sure it is possible in the example above.
Also, for each field , you can specify another object (see 4 parameter for addField method).

If you do not need this feature, you can specify in the configuration of the field `'mapping' => false`.

### 2.3. Input types

Supported input types: checkbox, text, textarea, timestamp, html, password, select, file, number, image, address, tag, category.

Type **checkbox** has following settings:

| Parameter      | Parameter description                                                     | Default value               |
| -------------- | ------------------------------------------------------------------------- | --------------------------- |
| description    | Field description, which will be displayed to the user as the field name  | such as field name          |
| required       | If true field is required                                                 | false                       |
| requiredError  | Text for the "required" error                                             | "The field must be checked" |
| uncheckedValue | Value for unchecked condition                                             | false                       |
| checkedValue   | Value for checked condition                                               | true                        |
| disabled       | Set field to disabled state                                               | false                       |

Type **text** has following settings:

| Parameter                  | Parameter description                                                     | Default value                |
| -------------------------- | ------------------------------------------------------------------------- | ---------------------------- |
| description                | Field description, which will be displayed to the user as the field name  | such as field name           |
| required                   | If true field is required                                                 | false                        |
| requiredError              | Text for the "required" error                                             | "The field can not be empty" |
| regexp                     | Regular expression with which the entered text will be checked            | "/\^[\s\S]*$/i"               |
| regexpError                | Text for the "regular expression" error                                   | "The field is not valid"     |
| validateCallback           | User-defined function to check the field (see below)                      | null                         |
| validateCallbackParameters | Additional parameters for validateCallback function                       | null                         |
| uniqueInDoctrine           | Enables checking the uniqueness of the field in the Doctrine database     | false                        |
| uniqueError                | Text for the "unique" error                                               | "This value already exists in the database" |       
| disabled                   | Set field to disabled state                                               | false                        |

Type **textarea** has following settings:

| Parameter                  | Parameter description                                                     | Default value                |
| -------------------------- | ------------------------------------------------------------------------- | ---------------------------- |
| description                | Field description, which will be displayed to the user as the field name  | such as field name           |
| required                   | If true field is required                                                 | false                        |
| requiredError              | Text for the "required" error                                             | "The field can not be empty" |
| regexp                     | Regular expression with which the entered text will be checked            | "/\^[\s\S]*$/i"               |
| regexpError                | Text for the "regular expression" error                                   | "The field is not valid"     |
| disabled                   | Set field to disabled state                                               | false                        |

Type **timestamp** has following settings:

| Parameter       | Parameter description                                                                      | Default value                |
| --------------- | ------------------------------------------------------------------------------------------ | ---------------------------- |
| description     | Field description, which will be displayed to the user as the field name                   | such as field name           |
| required        | If true field is required                                                                  | false                        |
| requiredError   | Text for the "required" error                                                              | "The field can not be empty" |
| dateTimeFormat  | Timestamp format accepted by [date()](http://php.net/manual/en/function.date.php) function | "Y-m-d\TH:i:s"               |
| formatError     | Text for the "datetime format" error                                                       | "Bad datetime format"        |
| timeZone        | Timezone (string or \DateTimeZone or null)                                                 | null                         |
| disabled        | Set field to disabled state                                                                | false                        |

Type **html** has following settings:

| Parameter                  | Parameter description                                                     | Default value                |
| -------------------------- | ------------------------------------------------------------------------- | ---------------------------- |
| description                | Field description, which will be displayed to the user as the field name  | such as field name           |
| required                   | If true field is required                                                 | false                        |
| requiredError              | Text for the "required" error                                             | "The field can not be empty" |
| disableFilters             | Turns off all HTML filters, the field value is passed as is               | false                        |
| allowTags                  | If filled all tags other than listed, will be removed (format: "h1,p,a")  | null                         |
| allowStyleProperty         | If false all "style" attributes will be removed                           | true                         |
| replaceUrl                 | If true all links will be replaced with JavaScript calls or redirect      | false                        |
| replaceUrlPath             | Links will be replaced with redirect to this URL with "path" get-parameter | null                        |
| disabled                   | Set field to disabled state                                               | false                        |

Type **password** has following settings:

| Parameter                  | Parameter description                                                     | Default value                |
| -------------------------- | ------------------------------------------------------------------------- | ---------------------------- |
| description                | Field description, which will be displayed to the user as the field name  | such as field name           |
| required                   | If true field is required                                                 | false                        |
| requiredError              | Text for the "required" error                                             | "The field can not be empty" |
| encoder                    | PasswordEncoderInterface to encode password, or null, or "@factory" to use security.encoder_factory service | null                         |
| repeat                     | If true it requires password repetition                                   | false                        |
| repeatError                | Text for the "repeat" error                                               | "Values​do not match"        |
| repeatDescription          | Repeat field description, which will be displayed to the user             | ""                           |
| mapNullValues              | If false, the mapping property will not change when field is empty        | true                         |
| regexp                     | Regular expression with which the entered text will be checked            | "/\^[\S]{5,99}$/i"           |
| regexpError                | Text for the "regular expression" error                                   | "The field is not valid"     |
| randomizeSalt              | If true salt will be replaces by random value                             | true                         |
| mappingSaltProperty        | Property name to store salt value in mapping object                       | ""                           |
| disabled                   | Set field to disabled state                                               | false                        |

Type **select** has following settings:

| Parameter        | Parameter description                                                                        | Default value                 |
| ---------------- | -------------------------------------------------------------------------------------------- | ----------------------------- |
| description      | Field description, which will be displayed to the user as the field name                     | such as field name            |
| required         | If true field is required                                                                    | false                         |
| requiredError    | Text for the "required" error                                                                | "The field can not be empty"  |
| choices          | An array of possible values                                                                  | array()                       |
| choicesError     | Text for the error when value do not find in *choices* array                                 | "The field contain bad value" |
| multiply         | If true possible to select multiple values                                                   | false                         |
| expanded         | If true *select* will show extended (radio buttons, checkboxes)                              | false                         |
| explodeValue     | Only for multiply select: if true output value will be exploded to string (if false - array) | false                         |
| explodeSeparator | Explode separator (when *explodeValue* is true)                                              | ","                           |
| disabled         | Set field to disabled state                                                                  | false                         |

Type **file** has following settings:

| Parameter          | Parameter description                                                               | Default value                      |
| ------------------ | ----------------------------------------------------------------------------------- | ---------------------------------- |
| description        | Field description, which will be displayed to the user as the field name            | such as field name                 |
| required           | If true field is required                                                           | false                              |
| requiredError      | Text for the "required" error                                                       | "The field can not be empty"       |
| maxSize            | Maximum allowed file size (null for unlimited)                                      | null                               |
| maxSizeError       | Text for oversize error                                                             | "File size is larger than allowed" |
| mimeTypes          | Array of allowed MIME types (null for disable filter)                               | null                               |
| mimeTypesError     | Text for MIME type error                                                            | "Invalid file type"                |
| storeType          | Type of file store (see below)                                                      | ST_FILE                            |
| storeFolder        | Folder for saving files                                                             | "uploads"                          |
| storeDoctrineClass | Doctrine file entity class (must implements Sergsxm\UIBundle\Classes\FileInterface) | ""                                 |
| multiply           | If true possible to upload multiply files in one field                              | false                              |
| disabled           | Set field to disabled state                                                         | false                              |

Type **number** has following settings:

| Parameter                  | Parameter description                                                     | Default value                |
| -------------------------- | ------------------------------------------------------------------------- | ---------------------------- |
| description                | Field description, which will be displayed to the user as the field name  | such as field name           |
| required                   | If true field is required                                                 | false                        |
| requiredError              | Text for the "required" error                                             | "The field can not be empty" |
| decimalPoint               | Sets the separator for the decimal point                                  | "."                          |
| thousandSeparator          | Sets the thousands separator                                              | ""                           |
| decimals                   | Sets the number of decimal points (or null for disable option)            | null                         |
| minValue                   | Sets minimal value (or null for disable option)                           | null                         |
| maxValue                   | Sets maximal value (or null for disable option)                           | null                         |
| valueError                 | Text for the "limits" error                                               | "The number is beyond the set limits" |
| notNumberError             | Text for the "not a number" error                                         | "This is not a number"       |
| disabled                   | Set field to disabled state                                               | false                        |

Type **image** has following settings:

| Parameter          | Parameter description                                                               | Default value                      |
| ------------------ | ----------------------------------------------------------------------------------- | ---------------------------------- |
| description        | Field description, which will be displayed to the user as the field name            | such as field name                 |
| required           | If true field is required                                                           | false                              |
| requiredError      | Text for the "required" error                                                       | "The field can not be empty"       |
| maxSize            | Maximum allowed file size (null for unlimited)                                      | null                               |
| maxSizeError       | Text for oversize error                                                             | "File size is larger than allowed" |
| minWidth           | Minimal image width (null for unlimited)                                            | null                               |
| minHeight          | Minimal image height (null for unlimited)                                           | null                               |
| maxWidth           | Maximal image width (null for unlimited)                                            | null                               |
| maxHeight          | Maximal image height (null for unlimited)                                           | null                               |
| imageSizeError     | Text for image size error                                                           | "Wrong image size"                 |
| notImageError      | Text for "not an image" error                                                       | "The file is not an image"         |
| storeType          | Type of file store (see below)                                                      | ST_FILE                            |
| storeFolder        | Folder for saving files                                                             | "uploads"                          |
| storeDoctrineClass | Doctrine file entity class (must implements Sergsxm\UIBundle\Classes\ImageInterface) | ""                                |
| multiply           | If true possible to upload multiply images in one field                             | false                              |
| disabled           | Set field to disabled state                                                         | false                              |

Type **address** has following settings:

| Parameter                  | Parameter description                                                     | Default value                |
| -------------------------- | ------------------------------------------------------------------------- | ---------------------------- |
| description                | Field description, which will be displayed to the user as the field name  | such as field name           |
| required                   | If true field is required                                                 | false                        |
| requiredError              | Text for the "required" error                                             | "The field can not be empty" |
| mapEnabled                 | If true map is enabled in field                                           | false                        |
| mappingCoordinatesProperty | Property name to store coordinates from map                               | null                         |
| disabled                   | Set field to disabled state                                               | false                        |

Type **tag** has following settings:

| Parameter                  | Parameter description                                                     | Default value                |
| -------------------------- | ------------------------------------------------------------------------- | ---------------------------- |
| description                | Field description, which will be displayed to the user as the field name  | such as field name           |
| required                   | If true field is required                                                 | false                        |
| requiredError              | Text for the "required" error                                             | "The field can not be empty" |
| doctrineClass              | Doctrine tag entity class (must inplements Sergsxm\UIBundle\Classes\TagInterface) | null                 |
| tagProperty                | Property name in entity class to find tags in database                    | 'tag'                        |
| disabled                   | Set field to disabled state                                               | false                        |

Type **category** has following settings:

| Parameter        | Parameter description                                                                        | Default value                 |
| ---------------- | -------------------------------------------------------------------------------------------- | ----------------------------- |
| description      | Field description, which will be displayed to the user as the field name                     | such as field name            |
| required         | If true field is required                                                                    | false                         |
| requiredError    | Text for the "required" error                                                                | "The field can not be empty"  |
| categories       | An array of categories (each element must implements Sergsxm\UIBundle\Classes\TreeInterface) | array()                       |
| categoriesError  | Text for the error when value do not find in *categories* array                              | "The field contain bad value" |
| multiply         | If true possible to select multiple categories                                               | false                         |
| expanded         | If true *select* will show extended (radio buttons, checkboxes)                              | false                         |
| mapIdToValue     | If true only category ID will be placed as value of mapping property                         | false                         |
| loadDoctrineRepository | Allow to load categories from Doctrine repository (otherwise - by *categories* parameter) | null                       |
| disabled         | Set field to disabled state                                                                  | false                         |

*ValidateCallback* function must be callable. The function should return null (if field value is valid) or error text. 
The first parameter passed to the function is the value of the field. 
The second parameter specifies by parameter *validateCallbackParameters*.

*StoreType* specifies the type of save file. 
Types defined by constants in the class \Sergsxm\UIBundle\FormInputTypes\File (\Sergsxm\UIBundle\FormInputTypes\Image for image type).
There are two types: ST_FILE and ST_DOCTRINE.
When you type ST_FILE file is saved in *storeFolder* folder.
The file with the extension info, which stores information about the file, will creates in the same folder.
Filename will be used as value for the mapping property.
When you type ST_DOCTRINE file is also stored in *storeFolder* folder.
File information is stored in a database in entity *storeDoctrineClass*.
This entity will be used as value for the mapping property.
*Note: storeFolder must be protected from external access for security.*

You can also specify a placeholder for the fields and the captcha through *placeholder* configuration parameter.

### 2.4. Captcha types

Supported captcha types: standart.

Type **standart** has following settings:

| Parameter      | Parameter description                                                             | Default value               |
| -------------- | --------------------------------------------------------------------------------- | --------------------------- |
| description    | Captcha field description, which will be displayed to the user as the field name  | such as field name          |
| validateError  | Text for validation error                                                         | "Values do not match"       |
| width          | Captcha image width (in pixels)                                                   | 150                         |
| height         | Captcha image height (in pixels)                                                  | 50                          |
| background     | Background color for captcha image (CSS like string)                              | "fff"                       |
| color          | Main color for captcha image (CSS like string)                                    | "000"                       |
| noise          | Enable noise lines                                                                | false                       |

### 2.5. Advanced templating

Default simple form\`s template are placed in **SergsxmUIBundle:Form:Form.html.twig**.
Field\`s and group\`s parameters are transferred to the group default template **SergsxmUIBundle:Form:FormGroup.html.twig**.
In this template, field\`s parameters are transferre to the field default templates, witch places into **SergsxmUIBundle:FormInputTypes:**.
You can change the default templates, but you can describe the output of individual fields in the group template:

```twig
{% if root|default(false) == false %}
    <div id="{{groupId}}">
        {% if description != '' %}
            <h2>{{description}}</h2>
        {% endif %}
{% endif %}
        {% for field in fields %}
            {% if field['type'] == 'text' %}
                ...
                ...
            {% else %}
                {% include field['defaultTemplate'] with field only %}
            {% endif %}
        {% endfor %}
        {% for group in groups %}
            {% include group['defaultTemplate'] with group only %}
        {% endfor %}
{% if root|default(false) == false %}
    </div>
{% endif %}
```

So you can create your own templates and put them when calling the render methods of form object.

You can also specify a template for the fields and the captcha through *template* configuration parameter.

### 2.6. Annotations

You can create forms from the annotations of mapping object. 
For this purpose use method `fromAnnotations($formName = null, $mappingObject = self::MO_PARENT)`.

Annotation `Sergsxm\UIBundle\Annotations\FormField` is used to create input fields.
Type and configuration parameters are directly passed to the method *addField*.

Example:

```php
    /**
     * @var string
     *
     * @\Sergsxm\UIBundle\Annotations\FormField(type="text", configuration={"description"="File name", "required"=true, "requiredError"="The field can not be empty"})
     */
    private $fileName;
```

To localize the phrases in annotation there is option: translate. 
Option *translate* is an array that contains the keys of the configuration array that need to be localized.
Also you can specified translator domain by 'Sergsxm\UIBundle\Annotations\TranslationDomain' class annotation.

Example:

```php
/**
 * @\Sergsxm\UIBundle\Annotations\TranslationDomain("sergsxmui")
 */
class FileEntity
{
    /**
     * @var string
     *
     * @\Sergsxm\UIBundle\Annotations\Description("File name field")
     * @\Sergsxm\UIBundle\Annotations\FormField(
     *      type="text", 
     *      configuration={"description"="File name", "required"=true, "requiredError"="The field can not be empty"}, 
     *      translate={"description", "requiredError"})
     */
    private $fileName;
```

Annotation `Sergsxm\UIBundle\Annotations\Description` is used to set field description. This value will be translated.

Class annotation `Sergsxm\UIBundle\Annotations\Form` is used to setup forms of object.
There are three parameters: name, groups and fields.
Parameter *name* contains form name. When you call a method `fromAnnotations($formName)` form with this name will be loaded.
Parameter *groups* contains information about all groups.
Parameter *fields* contains form structure.

Example:

```php
/**
 * @\Sergsxm\UIBundle\Annotations\Form(
 *      name="user",
 *      groups={
 *          {"name"="first", "description"="First group"},
 *          {"name"="second", "description"="Second group"}
 *      },
 *      fields={"fileName", "first"={"contentFile"}, "second"={"mimeType"}}
 * )
 * @\Sergsxm\UIBundle\Annotations\TranslationDomain("sergsxmui")
 */
class FileEntity
{
    /**
     * @var string
     *
     * @\Sergsxm\UIBundle\Annotations\Description("File name")
     * @\Sergsxm\UIBundle\Annotations\FormField(
     *      type="text",
     *      configuration={"required"=true, "requiredError"="Field required"}, 
     *      translate={"requiredError"})
     */
    private $fileName;

    /**
     * @var string
     *
     * @\Sergsxm\UIBundle\Annotations\FormField(type="text", configuration={"description"="MIME", "required"=true, "requiredError"="Field required"})
     */
    private $mimeType;

    /**
     * @var string
     *
     * @\Sergsxm\UIBundle\Annotations\FormField(type="text", configuration={"description"="Content file"})
     */
    private $contentFile;

```

## 3. Table lists usage

### 3.1. Table list creation

To create the table list use the service:

```php
$list = $this->get('sergsxm.ui')->createTableList();
```

Now you have TableList (Sergsxm\UIBundle\TableList\TableList) object. This object contains following methods:

```php

public function addTab($repository, $name, $description = null);
public function getTab($name);
public function selectTab($name);
public function addColumn($type, $name, $configuration = array());
public function addUrlAction($name, $url, $configuration = array());
public function addAjaxAction($name, $sql, $configuration = array());
public function bindRequest(Request $request = null);
public function getView();
public function renderView($template = 'SergsxmUIBundle:TableList:TableList.html.twig', $ajaxTemplate = 'SergsxmUIBundle:TableList:TableListAjax.html.twig', $parameters = array());
public function render($template = 'SergsxmUIBundle:TableList:TableList.html.twig', $ajaxTemplate = 'SergsxmUIBundle:TableList:TableListAjax.html.twig', $parameters = array(), Response $response = null);
```

In a few words, when you create the tab, you specified Doctrine repository. 
When you add any column as the name specified the name of the field in the repository.
Thus it is possible to build a simple table. For advanced tasks see section *Advanced queries*.

Assigning functions described in greater detail in the class file. 

Below is a code example of using table lists:

```php
$list = $this->get('sergsxm.ui')->createTableList();

$list
    ->addTab('TestBundle:FileEntity', 'files', 'Files')
    ->addColumn('text', 'fileName', array('description' => 'File name', 'searchEnabled' => true))
    ->addColumn('text', 'mimeType', array('description' => 'MIME type'))
    ->addColumn('timestamp', 'uploadDate', array('description' => 'Upload date'))
    ->addAjaxAction('delete', 'DELETE FROM TestBundle:FileEntity f WHERE f.id IN (:ids)', array('confirmed' => true))
    ->addAjaxAction('set_jpeg', 'UPDATE TestBundle:FileEntity f SET f.mimeType = \'image/jpeg\' WHERE f.id IN (:ids)')
    ->addAjaxAction('set_png', 'UPDATE TestBundle:FileEntity f SET f.mimeType = \'image/png\' WHERE f.id IN (:ids)')
    ->addUrlAction('new', '/admin/edit')
    ->addTab('TestBundle:FileEntity', 'files2', 'Files 2')
    ->addColumn('text', 'fileName', array('description' => 'File name', 'searchEnabled' => true))
    ->addColumn('text', 'mimeType', array('description' => 'MIME type'))
    ->addColumn('timestamp', 'uploadDate', array('description' => 'Upload date'))
    ->addAjaxAction('delete', 'DELETE FROM TestBundle:FileEntity f WHERE f.id IN (:ids)', array('confirmed' => true))
    ->addAjaxAction('set_jpeg', 'UPDATE TestBundle:FileEntity f SET f.mimeType = \'image/jpeg\' WHERE f.id IN (:ids)')
    ->addAjaxAction('set_png', 'UPDATE TestBundle:FileEntity f SET f.mimeType = \'image/png\' WHERE f.id IN (:ids)')
    ->addUrlAction('new', '/admin/edit')
    ;
        
$list->bindRequest();
        
return $list->render('SergsxmUIBundle:TableList:TableList.html.twig', 'SergsxmUIBundle:TableList:TableListAjax.html.twig');
```

### 3.2. Column types

Supported column types: text, checkbox, select, timestamp, file, number, image, tag, category.

All column types has following settings:

| Parameter      | Parameter description                                                                | Default value               |
| -------------- | ------------------------------------------------------------------------------------ | --------------------------- |
| description    | Column description, which will be displayed at the table head                        | such as column name         |
| url            | Link to any page, for example edit page of the row item (format describes below)     | null                        |
| hidden         | Off column display                                                                   | false                       |

Type **text** has following personal settings:

| Parameter      | Parameter description                                                                | Default value               |
| -------------- | ------------------------------------------------------------------------------------ | --------------------------- |
| orderEnabled   | Enables ordering for column                                                          | true                        |
| searchEnabled  | Enables search for column                                                            | false                       |
| pattern        | Text pattern. In this string statment "{{text}}" will be replaced by cell value      | "{{text}}"                  |

Type **checkbox** has following personal settings:

| Parameter        | Parameter description                                                                | Default value               |
| ---------------- | ------------------------------------------------------------------------------------ | --------------------------- |
| uncheckedValue   | Value for unchecked condition                                                        | false                       |
| checkedValue     | Value for checked condition                                                          | true                        |
| orderEnabled     | Enables ordering for column                                                          | true                        |
| uncheckedPattern | Text pattern for unchecked value                                                   | "<i class="fa fa-times"></i>" |
| checkedPattern   | Text pattern for checked value                                                     | "<i class="fa fa-check"></i>" |

Type **select** has following personal settings:

| Parameter         | Parameter description                                                          | Default value                 |
| ----------------- | ------------------------------------------------------------------------------ | ----------------------------- |
| choices           | An array of possible values                                                    | array()                       |
| multiply          | If true possible to select multiple values                                     | false                         |
| explodeValue      | Only for multiply select: if true value exploded to string (if false - array)  | false                         |
| explodeSeparator  | Explode separator (when *explodeValue* is true)                                | ","                           |
| orderEnabled      | Enables ordering for column                                                    | true                          |
| implodeSeparator  | Implode separator for output value                                             | ","                           |

Type **timestamp** has following personal settings:

| Parameter      | Parameter description                                                                      | Default value               |
| -------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| dateTimeFormat | Timestamp format accepted by [date()](http://php.net/manual/en/function.date.php) function | "Y-m-d\TH:i:s"              |
| timeZone       | Timezone (string or \DateTimeZone or null)                                                 | null                        |
| orderEnabled   | Enables ordering for column                                                                | true                        |

Type **file** has following personal settings:

| Parameter         | Parameter description                                                                      | Default value               |
| ----------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| storeType         | Type of file store (see file form field description)                                       | ST_FILE                     |
| multiply          | If true possible to upload multiply files in one field                                     | false                       |
| implodeSeparator  | Implode separator for output value                                                         | ","                         |
| fileUrl           | Link to any file page, for example edit or download file (format like for url parameter)   | null                        |

Type **number** has following personal settings:

| Parameter         | Parameter description                                                     | Default value                |
| ----------------- | ------------------------------------------------------------------------- | ---------------------------- |
| decimalPoint      | Sets the separator for the decimal point                                  | "."                          |
| thousandSeparator | Sets the thousands separator                                              | ""                           |
| decimals          | Sets the number of decimal points (or null for disable option)            | null                         |
| orderEnabled      | Enables ordering for column                                               | true                         |

Type **image** has following personal settings:

| Parameter         | Parameter description                                                                      | Default value               |
| ----------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| storeType         | Type of image store (see image form field description)                                     | ST_FILE                     |
| multiply          | If true possible to upload multiply files in one field                                     | false                       |
| implodeSeparator  | Implode separator for output value                                                         | ","                         |
| imageUrl          | Link to download image page (format like for url parameter)                                | null                        |

Type **tag** has following personal settings:

| Parameter         | Parameter description                                                                      | Default value               |
| ----------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| implodeSeparator  | Implode separator for output value                                                         | " "                         |
| pattern           | Pattern for one tag (string statment {{tag}} will be replaced by tag name) | "<span class="label sergsxmui-label">{{tag}}</span>" |

Type **category** has following personal settings:

| Parameter         | Parameter description                                                                      | Default value               |
| ----------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| categories      | An array of categories (each element must implements Sergsxm\UIBundle\Classes\TreeInterface) | array()                     |
| multiply          | If true possible to contain multiple categories                                            | false                       |
| mapIdToValue      | If true only category ID placed as value of mapping property                               | false                       |
| loadDoctrineRepository | Allow to load categories from Doctrine repository (otherwise - by *categories* parameter) | null                    |
| implodeSeparator  | Implode separator for output value                                                         | ","                         |
| categoryUrl       | Link to any category page, for example view or edit category (format like for url parameter) | null                      |

Parameter *url* can be two formats. 
One of formats is string with custom URL (detecting by "/" symbol). In this string statment "{{id}}" will be replaced by row item ID.
Second format is string with Symfony route. This string will by processed by Symfony routing service (with route parameter "id").

### 3.3. URL actions

Actions are displayed as a list of buttons. There are two types of actions: URL and ajax.
URL actions are simple links to other pages on your site (for example, to create a new page).
Ajax actions are actions on the selected items in table list. They are processed through the ajax request.

URL actions are added by method `addUrlAction($name, $url, $configuration = array());`. 
Parameter $name contains technical name of action. 
Parameter $url is action URL or route name.

URL actions has following additional configuration settings:

| Parameter        | Parameter description                                                                      | Default value               |
| ---------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| description      | Action description, which will be displayed at the button                                  | such as action name         |
| permission       | Allows to turn off the button, if it is prohibited by user permissions                     | true                        |
| sendIds          | If true checked IDs will be sent to a URL through a GET request                            | false                       |
| multiply         | If true it may be checked a lot of rows, if false it is to be checked only one row         | true                        |
| confirmed        | If true action requires confirmation                                                       | false                       |
| confirmedMessage | Сonfirmation message                                                                       | "Please confirm this operation" |
| confirmedTitle   | Сonfirmation window title                                                                  | "Warning"                   |
| confirmedOk      | Label for OK button                                                                        | "OK"                        |
| confirmedCancel  | Label for cancel button                                                                    | "Cancel"                    |

### 3.4. Ajax actions

Ajax actions are added by method `addAjaxAction($name, $sql, $configuration = array());`. 
Parameter $name contains technical name of action. 
Parameter $sql contains DQL query. It may be a few queries separated by ";". The query can contain parameters :id or :ids.

Ajax actions has following additional configuration settings:

| Parameter        | Parameter description                                                                      | Default value               |
| ---------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| description      | Action description, which will be displayed at the button                                  | such as action name         |
| permission       | Allows to turn off the button, if it is prohibited by user permissions                     | true                        |
| multiply         | If true it may be checked a lot of rows, if false it is to be checked only one row         | true                        |
| confirmed        | If true action requires confirmation                                                       | false                       |
| confirmedMessage | Сonfirmation message                                                                       | "Please confirm this operation" |
| confirmedTitle   | Сonfirmation window title                                                                  | "Warning"                   |
| confirmedOk      | Label for OK button                                                                        | "OK"                        |
| confirmedCancel  | Label for cancel button                                                                    | "Cancel"                    |
| callback         | Callback function (see below)                                                              | null                        |

When the DQL query is not enough (for example, to verify user permissions for each row), use the callback function. 
This function is passed an array of identifiers of selected rows.
The function should return an errors array (key - row identifier, value - error text).

### 3.5. Advanced queries

When you create the tab, you specified Doctrine repository. 
When you add any column as the name specified the name of the field in the repository.
As a result, the query will look like this `SELECT item.id, item.{{columnName1}} as col0, item.{{columnName2}} as col1 .... FROM {{repository}} item`.

It has the ability to perform subqueries. To do this, as variable $name write a subquery:

```
    ->addColumn('number', 'SELECT COUNT(m.id) FROM TestBundle:TestEntity m WHERE m.fooFile = item.id', array('description' => 'Use count'))
```

Also, it has the ability to perform join statments. To do this, as variable $name write a select part and add 'join' parameter:

```
    ->addColumn('image', 'file.contentFile JOIN item.fooFile file', array('description' => 'Image'))
```

## 4. Tree and order forms usage

### 4.1. Tree and order form creation

To create the tree form use the service:

```php
$list = $this->get('sergsxm.ui')->createTreeForm($configuration, $treeItems);
```

To create the order form use the service:

```php
$list = $this->get('sergsxm.ui')->createOrderForm($configuration, $orderListItems);
```

First parameter is a configuration array. Second parameter is array of list items (you may also load all Doctrine repository by specified *loadDoctrineRepository* configuration parameter).

Now you have TreeForm (Sergsxm\UIBundle\TreeForm\TreeForm) or OrderForm (Sergsxm\UIBundle\OrderForm\OrderForm) object. 

TreeForm object contains following methods:

```php
public function getView();
public function renderView($template = 'SergsxmUIBundle:TreeForm:TreeForm.html.twig', $parameters = array());
public function render($template = 'SergsxmUIBundle:TreeForm:TreeForm.html.twig', $parameters = array(), Response $response = null);
public function setReadOnly($readOnly);
public function getResult();
public function bindRequest(Request $request = null);
public function clear();
public function getFormId();
public function setFormId($formId);
```

OrderForm object contains following methods:

```php
public function getView();
public function renderView($template = 'SergsxmUIBundle:OrderForm:OrderForm.html.twig', $parameters = array());
public function render($template = 'SergsxmUIBundle:OrderForm:OrderForm.html.twig', $parameters = array(), Response $response = null);
public function setReadOnly($readOnly);
public function getResult();
public function bindRequest(Request $request = null);
public function clear();
public function getFormId();
public function setFormId($formId);
```

Below is a simple example of using tree forms:

```php
$treeForm = $this->get('sergsxm.ui')->createTreeForm(array(
    'createEnabled' => true,
    'createCallback' => array($this, 'createTree'),
    'removeEnabled' => true,
), $this->getDoctrine()->getRepository('\TestBundle\Entity\TreeEntity')->findAll());
        
if ($treeForm->bindRequest()) {
    $treeForm->clear();
    echo 'Data saved!';
}        
        
return $treeForm->render();
```

### 4.2. Tree and order item entity

Item entity for TreeForm must implements \Sergsxm\UIBundle\Classes\TreeInterface.  

Each tree entity has a parent field and the order field. Value of the parent field can be a parent entity or integer ID (set by configuration). Value of the order field is an integer.

There is also nested set interface \Sergsxm\UIBundle\Classes\TreeNSInterface that extends TreeInterface. 
This interface has additionaly methods to set level, left and right keys for nested set algorithms.

Item entity for OrderForm must implements \Sergsxm\UIBundle\Classes\OrderInterface.

Each order list entity has a order filed.

### 4.3. Configuration parameters

TreeForm has following configuration parameters:

| Parameter              | Parameter description                                                                      | Default value               |
| ---------------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| createCallback         | Parameter required when createEnabled is true. Function witch called for tree item creation (see below) | null           |
| changeCallback         | Function witch called after tree item is changed (see below)                               | null                        |
| removeCallback         | Function witch called after tree item is removed (see below)                               | null                        |
| url                    | Link to any page, for example edit page of the tree item (format describes below)          | null                        |
| createEnabled          | If true create function is enabled                                                         | false                       |
| removeEnabled          | If true remove function is enabled                                                         | false                       |
| readOnly               | If true tree form is locked for editing                                                    | false                       |
| mapIdToParentProperty  | If true value of the parent field is integer ID, if false - entity                         | false                       |
| loadDoctrineRepository | Allow to load tree items from Doctrine repository (otherwise, you must specify tree items when you create a form) | null |
| nestedSetFirstIndex    | First index value for nested set keys                                                      | 1                           |

Parameter *createCallback* must contains callback function with three parameters: $title, $parent, $order. 
$title is a title of new tree item. 
$parent is parent entity or null. 
$order is order of tree item in tree (integer).

Parameter *changeCallback* must contains callback function with three parameters: $item, $parent, $order.
$item is tree item entity.
$parent is parent entity or null. 
$order is order of tree item in tree (integer).
This function call after changing parent and order fields of entity.

Parameter *removeCallback* must contains callback function woth one parameter: $item.
$item is tree item entity to remove.
This function call after call of remove method of entity manager.

Parameter *url* can be two formats. 
One of formats is string with custom URL (detecting by "/" symbol). In this string statment "{{id}}" will be replaced by row item ID.
Second format is string with Symfony route. This string will by processed by Symfony routing service (with route parameter "id").

OrderForm has following configuration parameters:

| Parameter              | Parameter description                                                                      | Default value               |
| ---------------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| createCallback         | Parameter required when createEnabled is true. Function witch called for tree item creation (see below) | null           |
| changeCallback         | Function witch called after tree item is changed (see below)                               | null                        |
| removeCallback         | Function witch called after tree item is removed (see below)                               | null                        |
| url                    | Link to any page, for example edit page of the tree item (format describes above)          | null                        |
| createEnabled          | If true create function is enabled                                                         | false                       |
| removeEnabled          | If true remove function is enabled                                                         | false                       |
| readOnly               | If true tree form is locked for editing                                                    | false                       |
| loadDoctrineRepository | Allow to load list items from Doctrine repository (otherwise, you must specify list items when you create a form) | null |

Parameter *createCallback* must contains callback function with two parameters: $title, $order. 
$title is a title of new item. 
$order is order of item in order list (integer).

Parameter *changeCallback* must contains callback function with two parameters: $item, $order.
$item is order list item entity.
$order is order of item in order list (integer).
This function call after changing parent and order fields of entity.

Parameter *removeCallback* must contains callback function woth one parameter: $item.
$item is order list item entity to remove.
This function call after call of remove method of entity manager.

## 5. Notes

This bundle is just my own view of how should be organized in forms and lists. 
The code is few tested and may contain some bugs.

## 6. License

This bundle is under MIT license
