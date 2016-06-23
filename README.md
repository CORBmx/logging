# Installation
* First add the package to your project

    `composer require corb/logging`

* Add the service provider in your *config/app.php* file

    `Corb\Logging\LoggingServiceProvider::class`

* Pusblish package files

    `php artisan vendor:publish --provider="Corb\Logging\LoggingServiceProvider"`

    This will add a [configuration](#configuration) file in your *config/* directory
    named `logging.php`. All the required migrations will be also copied to your
    migrations folder.

    You can also pusblish these files individually using the tags `config` and `migrations`:

    `php artisan vendor:publish --tag=config --provider="Corb\Logging\LoggingServiceProvider"`

    `php artisan vendor:publish --tag=migrations --provider="Corb\Logging\LoggingServiceProvider"`

# Logging

The concept of logging, as it's used here, is to record specific *events* or mainly
*actions* that occurs in the application.  Some information about these actions is
also registered, generally **who** performed the action, **when** it was performed,
and the **subject** of that action; for example: "Matilda (*who*) invented (*action*)
a new month (*subject*) on 17/13/2016 (*when*)".  All of this information is stored
in the *activity_logs* table, and can be querying using the [ActivityLog](#activitylog)
model.

As there are some complexities about creating new logs, such the polymorphic relationships,
the [Loggeable](#loggeable) trait and the [Logger](#logger) class provide a simple
interface to do this.

### Log context

For certain actions there are some **contextual data**, apart from the general
information mentioned above, that we may want to store. The [*updated event*](#updated-event),
for example, registers the *before* and *after* states of the updated model instance.
As this contextual data is required only for that specific action, it's not stored
in the *activity_logs* table but in its own table. A table must be created for
each action we have contextual data for. In the case of the UPDATE action, this
data is stored in the *update_log_contexts* table. This way, if you want to store
extra data for a custom action of your own, it's necessary that you create its own
**context table**. There are some utilities that facilitates the inclusion of custom
actions, see the [Logging custom actions](#logging-custom-actions) on how to do this.

# Configuration

The *config/logging.php* file defines some options that you can chage to meet your needs.

## user_model

Indicates the user model for your application. This model is used as the responsible
when a new log is created.

`'user_model' => 'App\User'`

## auth_method

This method is expected to return the current authenticated user in your application.
It is used to automatically set the responsible id when an action is logged and a
responsible id is not provided. If null, this feature will be disabled.

`'auth_method' => '\Auth::user'`

## contexts

An array that defines the available [log contexts](#log-context) in your application.
Each element will define a one-to-one relationship in the `Corb\Logging\Models\ActivityLog`
model. The key indicates the name of the relationship, while the value is the model
to which the relationship points to. This way, you can access the context in each
of your logs using this property and also eager loading your contexts when querying
multiple logs.

```
'contexts' => [
    'update_context' => 'Corb\Logging\Models\UpdateLogContext'
]
```

## Actions

In the top of your config file, there's an abstract class `Actions` where all loggeable
actions are defined as const members. Here you can add your own actions or change
the predefined actions keys.

```
abstract class Actions
{
    const CREATE = 'create';
    const UPDATE = 'update';
    const DELETE = 'delete';
}
```

# Loggeable

The `Corb\Logging\Traits\Loggeable` trait allows the model that uses it to register
new entries in the *activity_logs* table.

```php
use Illuminate\Database\Eloquent\Model;
use Corb\Logging\Traits\Loggeable;

class MyLoggeableModel extends Model
{
    use Loggeable;
    ...
}
```

You can log actions over your model using the `createLog` method:

## createLog

```
public function createLog($action, $context = NULL, $responsible_id = NULL)
```

#### $action

The *$action* is a string that identifies the action performed. It can be any
string you want, but you should keep it simply as you can, for exaple: 'update'.
The dafault actions are registered as constants in the `Corb\Logging\Actions`
class under the `config/logging.php` file. You should consider register your own
actions here, this way you can prevent any typos by using `Actions::MY_ACTION`
to create and query your logs.

#### $context

Refers to the [log context](#log-context). It should be an instance of a model that
uses the `Corb\Logging\Traits\LogContext`. If not given, no context will be associated
with the log.

#### $responsible_id

Indicates the id of the user that performed the action. If `NULL`, the current logged
user will be assumed as responsible.

#### Examples

You can simply log an action over your model as follows:

```
// Within your loggeable model
$this->createLog('my_action');

// On a loggeable model instance
$loggeable->createLog('my_action');
```

For examples of creating logs with context, refer to [Logging custom actions](#logging-custom-actions).

## Model's events logging

By default the *created*, *updated* and *deleted* events of your model will be
logged when using this trait. If you want only certain or any of these events to
be logged, you can set a `log_events` array property in your model, specifying
which of these events must be logged.

For example, if you want the *created* and *updated* events to be logged, but not
the *deleted* event, you can use:

```
use Illuminate\Database\Eloquent\Model;
use Corb\Logging\Traits\Loggeable;

class MyLoggeableModel extends Model
{
    use Loggeable;

    // Which of the 'created', 'updated' and 'deleted' events should be logged
    protected $log_events = ['created', 'updated'];
    ...
}
```

This can be useful for various scenarios, such when you need to define your own
*update* event logic.

You may already notice that the default actions defined in the [Actions class](#actions)
correspond with these events: the values of the `CREATE`, `UPDATE` and `DELETE`
constants are used as actions when logging the *created*, *updated* and *deleted*
events respectively.

#### updated event

The updated event logs also store contextual data under the `update_log_contexts`
table used by the `Corb\Logging\Models\UpdateLogContext` model. This data consists
in two fields: `before` and `after`. Both are serilized objects that contains the
properties that were updated. `before` contains the original values while `after`
stores the updated values.

## Querying your model logs

To query your model logs, you should use the `activityLogs` dynamic property. It
defines a *one to many* relationship between the loggeable model and the
`Corb\Logging\Models\ActivityLog` model, so it can be used as a query builder.

You can retrieve all the *updated* event logs for a model as follows:

```
use Corb\Logging\Actions;
...
$loggeable = MyLoggeableModel::first();

$updates = $loggeable->activityLogs()->where('action', Actions::UPDATE)->get();
```

If you remember, the updated event has some contextual data, you can retrive this
data under the `update_context` dynamic property.

```php
$first_update = $updates->first()->update_context;

$first_update->update_context->before;
$first_update->update_context->after;
```

The `update_context` property defines a relationship between the `Corb\Logging\Models\ActivityLog`
model and the `Corb\Logging\Models\UpdateLogContext` model. The `update_context`
property is "lazy loaded", this means that the necessary queries to retrieve the
correspondig *update_log_contexts* record is executed until you access that property.
This can be harmful as you will execute an extra query for each log. You can prevent
this by [*eager loading*](https://laravel.com/docs/5.1/eloquent-relationships#eager-loading)
your logs context.

#### Eager loading log context
You should use the `with` method to load all your logs context in a single query:

```
$loggeable->activityLogs()->with('update_context')->where('action', Actions::UPDATE);
```

Now you can access the `update_context` property without executing extra queries.

# Loggger
The `Corb\Logging\Loggger` class defines a simple method `create` to record new
logs. It's prefered to use this method  alongside  the `createLog` method in the
[Loggeable](#loggeable) trait, instead of directly create logs using the `ActivityLog`
model, as these methods resolve the relationships between the log, the context log and
the loggeable instance.

## create
```
public static function create($action, $context = NULL, $responsible_id = NULL, $loggeable = NULL)
```
#### $action, $context, $responsible_id
The *$action*, *$context* and *$responsible_id* parameters serve the same purpose
as in the [createLog](#createlog) method.

#### $loggeable
Indicates the model on which the action was performed. The model it's expected
to use the `Corb\Logging\Traits\Loggeable` trait.

The objective of this method is to provide the maximum flexibilty possible when
creating new log entries, so you can, for example, create logs with either a model
attached to it or not, although this is not recommended.

#### Examples

```
use Corb\Logging\Logger;
...
Logger::create('my_action');
```

# ActivityLog
The `App\Models\ActivityLog` model abstracts the *activity_logs* table. The results
returned by the `activityLogs` property in the `Loggeable` trait are instances of
this model. These intances contain the general information of each log.

```
$log = $loggeable->activityLogs()->first()

$log->action      // the logged action
$log->responsible // user responsible for the action
$log->created_at  // log creation date
$log->loggeable   // the entity on which the action was perfomed
```

# User activity

You can access your user activity history, this is, all the logs where the user
appears as responsible. For this, add the `Corb\Logging\Traits\UserActivity` trait
in your user model.

```
use Illuminate\Foundation\Auth\User as Authenticatable;
use Corb\Logging\Traits\UserActivity;

class User extends Authenticatable
{
    use UserActivity;

    ...

```

This will add a method `activity` in your model, which defines a *HasMany* relationship
with the `ActivityLog` model, so you can query the user activity.

```
$user = User::first();

$activity = $user->activity()->orderBy('created_at', 'desc')->get();
```

# Logging custom actions

We've already seen that you can log any action you want by simply using a string
that identifies it when [creating the log](#createlog). Also we've mentioned that
you can propertly define these actions the [Actions](#actions) class defined in
*app/loggin.php*. The real problem comes when you need to store **contextual data** for
your actions logs. As said before, you need to store this data in it's own table,
in addition you'll also need to create a model for this table and register it in
the `contexts` array under you configuration file. Follow the next steps to properly
define a new action to log.

## Defining the new action
First of all, we need to register the new action. Go to the *app\logging.php* file
and add your action as a *const* member in the `Actions` abstract class:

```
...
abstract class Actions
{
    const UPDATE = 'update';
    const CREATE = 'create';
    const DELETE = 'delete';
    ...
    const MY_ACTION = 'my_action';
}
```

## The context table migrate
Now, if you want to store contextual data for your action logs, create a new migrate
for your context table. The only required column for a context table it's a foreing
key `activity_log_id` that will reference the log it belongs to on the *activity_logs*
table:

```
    Schema::create('my_action_log_contexts', function (Blueprint $table) {
        $table->increments('id');

        // context data
        $table->text('data');
        ...

        // add foreign key
        $table->integer('activity_log_id')->unsigned();
        $table->foreign('activity_log_id')->references('id')->on('activity_logs')
              ->onDelete('cascade');
    });
    ...

```

## The log context model
Next, create a model for you *context table*. Make sure you add the `Corb\Logging\Traits\LogContext`
trait to your model.

```
namespace MyApp\Models\MyActionLogContext;

use Illuminate\Database\Eloquent\Model;
use Corb\Logging\Traits\LogContext;

class MyActionLogContext extends Model
{
    use LogContext;

    protected $table = 'my_action_log_contexts';
    ...
```

## Enable eager loading
In order to allow [eager loading](#eager-loading-context) your log context, you
must add it to the `context` array in the *app/logging.php* configuration file.

```
    ...

    'contexts' => [
        'update_context'    => 'Corb\Logging\Models\UpdateLogContext',
        'my_action_context' => 'MyApp\Models\MyActionLogContext'
    ]
```

## Use it
Now you can create logs for your action and query them:

```
// Create a context instance
$context = new MyActionLogContext();

// Fill your context with the necessary data
$context->data = $data

// Log your action
$loggeable->createLog(Actions::MY_ACTION, $context);

//Query your logs
$log = $loggeable->activityLogs()->with('my_action_context')->where('action', Actions::MY_ACTION')->first();

$log->my_action_context->data;
$log->responsible
...
```
