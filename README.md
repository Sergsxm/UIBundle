# Sergsxm UIBundle

It\`s bundle for Symfony2 framework, which provides functions for creating forms and table lists. This is useful when creating a site\`s backend.

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

If you will change layout template, remember to add CSS and JS files on it.

### 1.1. Third-party modules

For the operation of the bundle following modules needed:

[Bootstrap](http://getbootstrap.com/)

[jQuery](https://jquery.com/)

[TinyMCE](https://www.tinymce.com/) (optionaly, used for html input type in forms)

## 2. Forms usage

### 2.1. Form creation

To create the form use the service:

```php
$form = $this->get('sergsxm.ui')->createForm($object);
```

$object is a object for properties mapping. Leave this parameter or set it to null if this functionality is not needed.

Now you have Form (Sergsxm\UIBundle\Forms\Form) object. This object contains following methods:

```php
public function addField($type, $name, $configuration = array(), $mappingObject = 'parent');
public function openGroup($name, $description = '', $condition = '');
public function closeGroup();
public function enableCaptcha($type, $configuration = array());
public function disableCaptcha();
public function getFormId();
public function setFormId($formId);
public function setReadOnly($readOnly);
public function bindRequest(Request $request = null);
public function getResult();
public function getView();
public function renderView($template = 'SergsxmUIBundle:Forms:Form.html.twig', $parameters = array());
public function render($template = 'SergsxmUIBundle:Forms:Form.html.twig', $parameters = array(), Response $response = null);
public function findInputByName($name);
public function getValue();
public function setValue($value);
public function fromAnnotations($tag = null);
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
}
        
return $form->render('SergsxmUIBundle:Forms:Form.html.twig', array('title' => 'New form', 'backUrl' => '/123'));
```

### 2.2. Object\`s properties mapping

If you create a form specified object for properties mapping, the form values are automatically transferred to the object properties with the same name. 
Make sure it is possible in the example above.
Also, for each field , you can specify another object (see 4 parameter for addField method).

If you do not need this feature, you can specify in the configuration of the field `'mapping' => false`.

### 2.3. Input types

Supported input types: checkbox, text, textarea, timestamp, html, password, select, file.

Type **checkbox** has following settings:

| Parameter      | Parameter description                                                     | Default value               |
| -------------- | ------------------------------------------------------------------------- | --------------------------- |
| description    | Field description, which will be displayed to the user as the field name  | such as field name          |
| required       | If true field is required                                                 | false                       |
| requiredError  | Text for the "required" error                                             | "The field must be checked" |
| uncheckedValue | Value for unchecked condition                                             | false                       |
| checkedValue   | Value for checked condition                                               | true                        |

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

Type **textarea** has following settings:

| Parameter                  | Parameter description                                                     | Default value                |
| -------------------------- | ------------------------------------------------------------------------- | ---------------------------- |
| description                | Field description, which will be displayed to the user as the field name  | such as field name           |
| required                   | If true field is required                                                 | false                        |
| requiredError              | Text for the "required" error                                             | "The field can not be empty" |
| regexp                     | Regular expression with which the entered text will be checked            | "/\^[\s\S]*$/i"               |
| regexpError                | Text for the "regular expression" error                                   | "The field is not valid"     |

Type **timestamp** has following settings:

| Parameter       | Parameter description                                                                      | Default value                |
| --------------- | ------------------------------------------------------------------------------------------ | ---------------------------- |
| description     | Field description, which will be displayed to the user as the field name                   | such as field name           |
| required        | If true field is required                                                                  | false                        |
| requiredError   | Text for the "required" error                                                              | "The field can not be empty" |
| dateTimeFormat  | Timestamp format accepted by [date()](http://php.net/manual/en/function.date.php) function | "Y-m-d\TH:i"                 |

Type **html** has following settings:

| Parameter                  | Parameter description                                                     | Default value                |
| -------------------------- | ------------------------------------------------------------------------- | ---------------------------- |
| description                | Field description, which will be displayed to the user as the field name  | such as field name           |
| required                   | If true field is required                                                 | false                        |
| requiredError              | Text for the "required" error                                             | "The field can not be empty" |
| disableFilters             | Turns off all HTML filters, the field value is passed as is               | false                        |
| allowTags                  | If filled all tags other than listed, will be removed (format: "h1,p,a")  | null                         |
| allowStyleProperty         | If false all "style" attributes will be removed                           | true                         |
| replaceUrl                 | If true all links will be replaced with JavaScript calls                  | false                        |

Type **password** has following settings:

| Parameter                  | Parameter description                                                     | Default value                |
| -------------------------- | ------------------------------------------------------------------------- | ---------------------------- |
| description                | Field description, which will be displayed to the user as the field name  | such as field name           |
| required                   | If true field is required                                                 | false                        |
| requiredError              | Text for the "required" error                                             | "The field can not be empty" |
| encoder                    | Symfony2 PasswordEncoderInterface to encode password, or null             | null                         |
| repeat                     | If true it requires password repetition                                   | false                        |
| repeatError                | Text for the "repeat" error                                               | "Values​do not match"        |
| repeatDescription          | Repeat field description, which will be displayed to the user             | ""                           |
| mapNullValues              | If false, the mapping property will not change when field is empty        | true                         |
| regexp                     | Regular expression with which the entered text will be checked            | "/\^[\S]{5,99}$/i"            |
| regexpError                | Text for the "regular expression" error                                   | "The field is not valid"     |
| randomizeSalt              | If true salt will be replaces by random value                             | true                         |
| mappingSaltProperty        | Property name to store salt value in mapping object                       | ""                           |

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

*ValidateCallback* function must be callable. The function should return null (if field value is valid) or error text. 
The first parameter passed to the function is the value of the field. 
The second parameter specifies by parameter *validateCallbackParameters*.

*StoreType* specifies the type of save file. 
Types defined by constants in the class \Sergsxm\UIBundle\FormInputTypes\File.
There are two types: ST_FILE and ST_DOCTRINE.
When you type ST_FILE file is saved in *storeFolder* folder.
The file with the extension info, which stores information about the file, will creates in the same folder.
Filename will be used as value for the mapping property.
When you type ST_DOCTRINE file is also stored in *storeFolder* folder.
File information is stored in a database in entity *storeDoctrineClass*.
This entity will be used as value for the mapping property.
*Note: storeFolder must be protected from external access for security.*

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

Default simple form\`s template are placed in **SergsxmUIBundle:Forms:Form.html.twig**.
Field\`s and group\`s parameters are transferred to the group default template **SergsxmUIBundle:FormGroup:FormGroup.html.twig**.
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

### 2.6. Annotations

You can create forms from the annotations of mapping object (Doctrine required). 
For this purpose use method `fromAnnotations($tag = null)`.

Annotation `Sergsxm\UIBundle\Annotations\Input` is used to create input fields.
Type and configuration parameters are directly passed to the method *addField*.

Example:

```php
    /**
     * @var string
     *
     * @\Sergsxm\UIBundle\Annotations\Input(type="text", configuration={"description"="File name", "required"=true, "requiredError"="The field can not be empty"})
     */
    private $fileName;
```

To localize the phrases in annotation there are two options: translate and translateDomain. 
Option *translate* is an array that contains the keys of the configuration array that need to be localized.
Option *translateDomain* is translation domain for tanslator service.

Example:

```php
    /**
     * @var string
     *
     * @\Sergsxm\UIBundle\Annotations\Input(
     *      type="text", 
     *      configuration={"description"="File name", "required"=true, "requiredError"="The field can not be empty"}, 
     *      translate={"description", "requiredError"}, 
     *      translateDomain="sergsxmui")
     */
    private $fileName;
```

Annotation `Sergsxm\UIBundle\Annotations\Tags` is used to mark form input fields. 
When you call a method `fromAnnotations($tag)` with specified tag, the form will contain only the fields marked with this tag.
This is useful to create forms for different user roles.

Example:

```php
    /**
     * @var string
     *
     * @\Sergsxm\UIBundle\Annotations\Tags(forms={"user", "administrator"})
     * @\Sergsxm\UIBundle\Annotations\Input(type="text", configuration={"description"="File name", "required"=true, "requiredError"="The field can not be empty"})
     */
    private $fileName;
```

## 3. Table lists usage

### 3.1. Table list creation

To create the table list use the service:

```php
$list = $this->get('sergsxm.ui')->createTableList();
```

Now you have TableList (Sergsxm\UIBundle\Classes\TableList) object. This object contains following methods:

```php

public function addTab($repository, $name, $description = null);
public function getTab($name);
public function selectTab($name);
public function addColumn($type, $name, $configuration = array());
public function addUrlAction($name, $url, $configuration = array());
public function addAjaxAction($name, $sql, $configuration = array());
public function bindRequest(Request $request = null);
public function getView();
public function renderView($template = 'SergsxmUIBundle:TableLists:TableList.html.twig', $ajaxTemplate = 'SergsxmUIBundle:TableLists:TableListAjax.html.twig', $parameters = array());
public function render($template = 'SergsxmUIBundle:TableLists:TableList.html.twig', $ajaxTemplate = 'SergsxmUIBundle:TableLists:TableListAjax.html.twig', $parameters = array(), Response $response = null);
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
    ->addColumn('text', 'fileName', array('description' => 'File name', 'search' => true))
    ->addColumn('text', 'mimeType', array('description' => 'MIME type'))
    ->addColumn('datetime', 'uploadDate', array('description' => 'Upload date'))
    ->addAjaxAction('delete', 'DELETE FROM TestBundle:FileEntity f WHERE f.id IN (:ids)', array('confirmed' => true))
    ->addAjaxAction('set_jpeg', 'UPDATE TestBundle:FileEntity f SET f.mimeType = \'image/jpeg\' WHERE f.id IN (:ids)')
    ->addAjaxAction('set_png', 'UPDATE TestBundle:FileEntity f SET f.mimeType = \'image/png\' WHERE f.id IN (:ids)')
    ->addUrlAction('new', '/admin/edit')
    ->addTab('TestBundle:FileEntity', 'files2', 'Files 2')
    ->addColumn('text', 'fileName', array('description' => 'File name', 'search' => true))
    ->addColumn('text', 'mimeType', array('description' => 'MIME type'))
    ->addColumn('datetime', 'uploadDate', array('description' => 'Upload date'))
    ->addAjaxAction('delete', 'DELETE FROM TestBundle:FileEntity f WHERE f.id IN (:ids)', array('confirmed' => true))
    ->addAjaxAction('set_jpeg', 'UPDATE TestBundle:FileEntity f SET f.mimeType = \'image/jpeg\' WHERE f.id IN (:ids)')
    ->addAjaxAction('set_png', 'UPDATE TestBundle:FileEntity f SET f.mimeType = \'image/png\' WHERE f.id IN (:ids)')
    ->addUrlAction('new', '/admin/edit')
    ;
        
$list->bindRequest();
        
return $list->render('SergsxmUIBundle:TableLists:TableList.html.twig', 'SergsxmUIBundle:TableLists:TableListAjax.html.twig');
```

### 3.2. Column types

Supported column types: text, number, datetime, image, boolean, case.

All column types has following settings:

| Parameter      | Parameter description                                                                | Default value               |
| -------------- | ------------------------------------------------------------------------------------ | --------------------------- |
| description    | Column description, which will be displayed at the table head                        | such as column name         |
| url            | Link to any page, for example edit page of the row item (format describes below)     | ""                          |
| join           | SQL join section (for advanced queries)                                              | ""                          |
| sort           | Enables ordering for column                                                          | true                        |
| search         | Enables search for column                                                            | false                       |
| hidden         | Off column display                                                                   | false                       |

Type **text** has following personal settings:

| Parameter      | Parameter description                                                                | Default value               |
| -------------- | ------------------------------------------------------------------------------------ | --------------------------- |
| pattern        | Text pattern. In this string statment "{{text}}" will be replaced by cell value      | "{{text}}"                  |

Type **number** has following personal settings:

| Parameter         | Parameter description                                 | Default value               |
| ----------------- | ----------------------------------------------------- | --------------------------- |
| decimals          | Sets the number of decimal points                     | 0                           |
| thousandSeparator | Sets the thousands separator                          | " "                         |
| decimalPoint      | Sets the separator for the decimal point              | ","                         |

Type **datetime** has following personal settings:

| Parameter      | Parameter description                                                                      | Default value               |
| -------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| format         | Timestamp format accepted by [date()](http://php.net/manual/en/function.date.php) function | "Y-m-d H:i"                 |

Type **case** has following personal settings:

| Parameter      | Parameter description                                                                      | Default value               |
| -------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| choices        | Array of possible values (format: value=>description)                                      | array()                     |

Type **image** is **text** type, default value of parameter *pattern* is "\<img src="{{text}}" />".

Type **boolean** is **case** type, default value of parameter *choices* is `array('false' => '<i class="fa fa-times"></i>', 'true' => '<i class="fa fa-check"></i>')`.

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

| Parameter      | Parameter description                                                                      | Default value               |
| -------------- | ------------------------------------------------------------------------------------------ | --------------------------- |
| description    | Action description, which will be displayed at the button                                  | such as action name         |
| permission     | Allows to turn off the button, if it is prohibited by user permissions                     | true                        |

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

Also, it has the ability to perform join statments. To do this, as variable $name write a select part and add 'join' parameter to the $configuration array:

```
    ->addColumn('image', 'file.contentFile', array('description' => 'Image', 'join' => 'JOIN item.fooFile file'))
```

## 4. Notes

This bundle is just my own view of how should be organized in forms and lists. 
The code is few tested and may contain some bugs.

## 5. License

This bundle is under MIT license
