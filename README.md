# Index
* [Installation](#installation)
* [Logging](#logging)
    * [Log context](#log-context)
* [Configuration](#configuration)

# Installation
* First add the package to your project

    `composer require corb/logging`

* Add the service provider in your *config/app.php* file

    `Corb\Logging\LoggingServiceProvider::class`

* Pusblish package files

    `php artisan vendor:publish --provider="Corb\Logging\LoggingServiceProvider"`

    This will add a [configuration](#configuration) file in your *config/* directory named `logging.php`. All the required migrations will be also copied to your migrations folder.

    You can also pusblish these files individually using the tags `config` and `migrations`.

    `php artisan vendor:publish --tag=config --provider="Corb\Logging\LoggingServiceProvider"`

    `php artisan vendor:publish --tag=migrations --provider="Corb\Logging\LoggingServiceProvider"`

# Logging
The concept of logging, as it's used here, is to record specific *events* or mainly *actions* that occurs in the application.  Some information about these actions is also registered, generally **who** performed the action, **when** it was performed, and the **subject** of that action; for example: "Matilda (*who*) invented (*action*) a new month (*subject*) on 17/13/2016 (*when*)".  All of this information is stored in the *activity_logs* table, and can be querying using the [ActivityLog](#activitylog-model) model.

As there are some complexities about creating new logs, such the polymorphic relationships, the [LoggeableTrait](#loggeabletrait) and the [Logger class](#logger-class) provide a simple interface to do this.

### Log context
For certain actions there are some **contextual data**, apart from the general information mentioned above, that we may want to store. The UPDATE action, for example, registers the *before* and *after* states of the updated model instance. As this contextual data is required only for that specific action, it's not stored in the *activity_logs* table but in its own table. A table must be created for each action we have contextual data for. In the case of the UPDATE action, this data is stored in the *log_context_updates* table. This way, if you want to store extra data for a custom action of your own, it's necessary that you create its own **context table**. There are some utilities that facilitates the inclusion of custom actions, see the [Logging custom actions](#logging-custom-actions) on how to do this.

# Configuration

The *config/logging.php* file defines some options that you can chage according to meet your needs.

## user_model

Indicates the user model for your application. This model is used as the responsible when a new log is created.

`'user_model' => 'App\User'`

## auth_method

This method is expected to return the current authenticated user in your application. It is used to automatically set the responsible id when an action is logged and a
responsible id is not provided. If null, this feature will be disabled.

`'auth_method' => '\Auth::user'`

## contexts
An array that defines the available [log contexts](#log-context) in your application. Each element will define a one-to-one relationship in the Corb\Logging\Models\ActivityLog model. The key indicates the name of the relationship, while the value is the model to which the relationship points to. This way, you can access the context in each of your logs using this property and also eager loading your contexts when querying multiple logs.

```
'contexts' => [
    'update_context' => 'Corb\Logging\Models\LogContextUpdate'
]
```

## Actions
In the top of your config file, there's an abstract class `Actions` where all loggeable actions are defined as const members.
Here you can add your own actions or change the predefined actions keys.

```
abstract class Actions
{
    const CREATE = 'create';
    const UPDATE = 'update';
    const DELETE = 'delete';
}
```

# LoggeableTrait
The `App\Logging\LoggeableTrait` allows the model that uses it to register new entries in the *activity_logs* table. It also sets specific model events (*created*, *updating* and *deleted*), so the predefined actions: CREATE, UPDATE and DELETE, are logged automatically.

```php
use Illuminate\Database\Eloquent\Model;
use App\Logging\Traits\LoggeableTrait;

class MyLoggeableModel extends Model
{
    use LoggeableTrait;
    ...
}
```

Once you do this, the actions mentioned above will be logged automatically. You can log other actions over your model using the `createLog` method:

## createLog

```
public function createLog($action, $context = NULL, $responsible_id = NULL)
```

#### $action
The *$action* its a string that identifies the action performed. It can be any string you want, but you should keep it simply as you can, for exaple: 'update'. The dafault actions are registered as constants in the `App\Logging\Log`class. You should consider register your own actions here, this way you can prevent any typos by using `Log::MY_ACTION` to create and query your logs. See the [Log class](#log-class) for more info about it.

#### $context
Refers to the [log context](#log-context). It should be an instance of a model that uses the `App\Logging\Traits\LogContextTrait`. If not given, no context will be associated with the log.

#### $responsible_id
Indicates the id of the user that performed the action. If `NULL`, the current logged user will be assumed as responsible.

#### Examples

You can simply log an action over your model as follows:

```
// Within your loggeable model
$this->createLog('my_action');

// On a loggeable model instance
$loggeable->createLog('my_action');
```

For examples of creating logs with context, refer to [Logging custom actions](#logging-custom-actions).
### Querying your model logs

To query your model logs, you should use the `activityLogs` dynamic property. It defines a *one to many* relationship between the loggeable model and the `App\Models\ActivityLog` model,  so it can be used as a query builder. For instance, you can retrieve all the update logs for a model as follows:

```
use App\Logging\Log;
...
$loggeable = MyLoggeableModel::find(1);

$updates = $loggeable->activityLogs()->where('action', Log::UPDATE)->get();
```

If you remember, the UPDATE action has some contextual data, you can retrive this data under the `update_context` dynamic property.

```
$first_update = $updates->first()->update_context;

$first_update->update_context->before;
$first_update->update_context->after;
```
The `update_context` property defines a relationship between the `App\Models\ActivityLog` model and the `App\Models\LogsContext\LogContextUpdate` model. The `update_context` property is "lazy loaded", this means that the necessary queries to retrieve the correspondig *log_context_update* record is executed until you access that property. This can be harmful as you will execute an extra query for each log. You can prevent this by [*eager loading*](https://laravel.com/docs/5.1/eloquent-relationships#eager-loading) your logs context.

#### Eager loading context
You should use the `with` method to load all your logs context in a single query:

```
$loggeable->activityLogs()->with('update_context')->where('action', Log::UPDATE);
```
Now you can access the `update_context` property without executing extra queries.

# Log class
The `App\Logging\Log` class defines a simple method `create` to record new logs. It's prefered to use this method  alongside  the `createLog` method in the [LoggeableTrait](#loggeabletrait) trait, instead of directly create logs using the `App\Models\ActivityLog` as these methods resolve the relationships between the log, the context log and the loggeable instance.

## create
```
public static function create($action, $context = NULL, $responsible_id = NULL, $loggeable = NULL)
```
#### $action, $context, $responsible_id
The *$action*, *$context* and *$responsible_id* parameters serve the same purpose as in the [createLog](#createlog) method.

#### $loggeable
Indicates the model on which the action was performed. The model it's expected to use the `App\Logging\Traits\LoggeableTrait` trait.

The objective of this method is to provide the maximum flexibilty possible when creating new log entries, so you can, for example, create logs with either a model attached to it or not, although this is not recommended.

#### Examples

```
use App\Logging\Log;
...
Log::create('my_action')
```

## Defined actions
The `App\Logging\Log` class can also be used to register your  actions, this way you have them in one place. These are stored as *const* members in the class so you can use them when creating or querying your logs in the form of `Log::ACTION`. Take the predefined CREATE, UPDATE and DELETE actions as an example:

```
class Log
{
    /**
     * Defined actions
     */
    const UPDATE = 'update';
    const CREATE = 'create';
    const DELETE = 'delete';
    ...
}
```

# ActivityLog model
The `App\Models\ActivityLog` model abstracts the *activity_logs* table. The results returned by the `activityLogs` property in the `LoggeableTrait` are instances of this model. These intances contain the general information of each log.

```
$log = $loggeable->activityLogs()->first()

$log->action            // the logged action
$log->responsible // user responsible for the action
$log->created_at   // when the log was created
$log->loggeable     // the entity on which the action was perfomed
```

# Logging custom actions
We've already seen that you can log any action you want by simply using a string  that identifies it when [creating the log](#createlog). Also we've mentioned that you can propertly [define those actions](#defined-actions) in the `App\Loggin\Log` class. The real problems comes when you need to store **contextual data**  for these actions logs. As said before, you need to store this data in it's own table, in addition you'll also need to create a model for this table and define a relationship between this model and the `App\Models\ActivityLog` model in order for it to work properly. Follow the next step to understand the process.

## Defining the new action
First of all, we need to register the new action. Go to the `app\Logging\Log.php` and add your action as *const* member in the `Log`class:

```
...
class Log
{
    /**
     * Defined actions
     */
    const UPDATE = 'update';
    const CREATE = 'create';
    const DELETE = 'delete';
    ...
    const MY_ACTION = 'my_action';
}
```

## The context table migrate
Now, if your want to store contextual data for your action logs, create a new migrate for your context table, it's recomended you name it using the format `log_context_action`. The only required column for a context table it's a foreing key `activity_log_id` that will reference the log it belongs to on the *activity_log* table:

```
        Schema::create('log_context_my_action', function (Blueprint $table) {
            $table->increments('id');

            // context data
            $table->text('data');
            ...

            // add foreign key
            $table->integer('activity_log_id')->unsigned();
            $table->foreign('activity_log_id')->references('id')->on('activity_logs')
                  ->onDelete('cascade');
        });

```

## The log context model
Next, create a model for you *context* table. This should be created in the `app\Models\LogsContext\` directory. Make sure you add the `App\Loggin\Traits\LogContextTrait` trait to your model.

```
namespace App\Models\LogContexts;

use Illuminate\Database\Eloquent\Model;
use App\Logging\Traits\LogContextTrait;

class LogContextMyAction extends Model
{
    use LogContextTrait;

    protected $table = 'log_context_my_action';
    ...
```

## Enable eager loading
In order to allow [eager loading](#eager-loading-context) your log context, you must add a new relationship in the `App\Models\ActivityLog` that references your model.

```
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    ...
    public function my_action_context()
    {
        return $this->hasOne('App\Models\LogContexts\LogContextMyAction');
    }
}
```

## Use it
Now you can create logs for your action and query them:

```
// Create a context instance
$context = new LogContextMyAction();

// Fill your context with the necessary data
$context.data = $data

// Log your action
$loggeable->createLog(Log::MY_ACTION, $context);

//Query your logs
$log = $loggeable->activityLogs()->with('my_action_context')->where('action', Log::MY_ACTION')->first();

$log->my_action_context->data;
$log->responsible
...
```
