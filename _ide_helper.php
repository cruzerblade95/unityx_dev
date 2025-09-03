<?php
/* @noinspection ALL */
// @formatter:off
// phpcs:ignoreFile

/**
 * A helper file for Laravel, to provide autocomplete information to your IDE
 * Generated for Laravel 12.24.0.
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 * @see https://github.com/barryvdh/laravel-ide-helper
 */
namespace Anhskohbo\NoCaptcha\Facades {
    /**
     */
    class NoCaptcha {
        /**
         * Render HTML captcha.
         *
         * @param array $attributes
         * @return string
         * @static
         */
        public static function display($attributes = [])
        {
            /** @var \Anhskohbo\NoCaptcha\NoCaptcha $instance */
            return $instance->display($attributes);
        }

        /**
         * @see display()
         * @static
         */
        public static function displayWidget($attributes = [])
        {
            /** @var \Anhskohbo\NoCaptcha\NoCaptcha $instance */
            return $instance->displayWidget($attributes);
        }

        /**
         * Display a Invisible reCAPTCHA by embedding a callback into a form submit button.
         *
         * @param string $formIdentifier the html ID of the form that should be submitted.
         * @param string $text the text inside the form button
         * @param array $attributes array of additional html elements
         * @return string
         * @static
         */
        public static function displaySubmit($formIdentifier, $text = 'submit', $attributes = [])
        {
            /** @var \Anhskohbo\NoCaptcha\NoCaptcha $instance */
            return $instance->displaySubmit($formIdentifier, $text, $attributes);
        }

        /**
         * Render js source
         *
         * @param null $lang
         * @param bool $callback
         * @param string $onLoadClass
         * @return string
         * @static
         */
        public static function renderJs($lang = null, $callback = false, $onLoadClass = 'onloadCallBack')
        {
            /** @var \Anhskohbo\NoCaptcha\NoCaptcha $instance */
            return $instance->renderJs($lang, $callback, $onLoadClass);
        }

        /**
         * Verify no-captcha response.
         *
         * @param string $response
         * @param string $clientIp
         * @return bool
         * @static
         */
        public static function verifyResponse($response, $clientIp = null)
        {
            /** @var \Anhskohbo\NoCaptcha\NoCaptcha $instance */
            return $instance->verifyResponse($response, $clientIp);
        }

        /**
         * Verify no-captcha response by Symfony Request.
         *
         * @param \Symfony\Component\HttpFoundation\Request $request
         * @return bool
         * @static
         */
        public static function verifyRequest($request)
        {
            /** @var \Anhskohbo\NoCaptcha\NoCaptcha $instance */
            return $instance->verifyRequest($request);
        }

        /**
         * Get recaptcha js link.
         *
         * @param string $lang
         * @param boolean $callback
         * @param string $onLoadClass
         * @return string
         * @static
         */
        public static function getJsLink($lang = null, $callback = false, $onLoadClass = 'onloadCallBack')
        {
            /** @var \Anhskohbo\NoCaptcha\NoCaptcha $instance */
            return $instance->getJsLink($lang, $callback, $onLoadClass);
        }

            }
    }

namespace Telegram\Bot\Laravel\Facades {
    /**
     * @see \Telegram\Bot\BotsManager
     * @see \Telegram\Bot\Api
     * @method static \Telegram\Bot\BotsManager manager(array $config)
     * @method static bool kickChatMember(array $params)
     * @method static bool banChatMember(array $params)
     * @method static string exportChatInviteLink(array $params)
     * @method static \Telegram\Bot\Objects\ChatInviteLink createChatInviteLink(array $params)
     * @method static \Telegram\Bot\Objects\ChatInviteLink editChatInviteLink(array $params)
     * @method static \Telegram\Bot\Objects\ChatInviteLink revokeChatInviteLink(array $params)
     * @method static bool approveChatJoinRequest(array $params)
     * @method static bool declineChatJoinRequest(array $params)
     * @method static bool setChatPhoto(array $params)
     * @method static bool deleteChatPhoto(array $params)
     * @method static bool setChatTitle(array $params)
     * @method static bool setChatDescription(array $params)
     * @method static bool pinChatMessage(array $params)
     * @method static bool unpinChatMessage(array $params)
     * @method static bool unpinAllChatMessages(array $params)
     * @method static bool leaveChat(array $params)
     * @method static bool unbanChatMember(array $params)
     * @method static bool restrictChatMember(array $params)
     * @method static bool promoteChatMember(array $params)
     * @method static bool setChatAdministratorCustomTitle(array $params)
     * @method static bool banChatSenderChat(array $params)
     * @method static bool unbanChatSenderChat(array $params)
     * @method static bool setChatPermissions(array $params)
     * @method static \Telegram\Bot\Objects\Chat getChat(array $params)
     * @method static array getChatAdministrators(array $params)
     * @method static int getChatMemberCount(array $params)
     * @method static \Telegram\Bot\Objects\ChatMember getChatMember(array $params)
     * @method static bool setChatStickerSet(array $params)
     * @method static bool deleteChatStickerSet(array $params)
     * @method static bool setMyCommands(array $params)
     * @method static bool deleteMyCommands(array $params = [])
     * @method static array getMyCommands(array $params = [])
     * @method static \Telegram\Bot\Commands\CommandBus getCommandBus()
     * @method static \Telegram\Bot\Api setCommandBus(\Telegram\Bot\Commands\CommandBus $commandBus)
     * @method static \Telegram\Bot\Objects\Update|array commandsHandler(bool $webhook = false, ?\Psr\Http\Message\RequestInterface $request = null)
     * @method static void processCommand(\Telegram\Bot\Objects\Update $update)
     * @method static mixed triggerCommand(string $name, \Telegram\Bot\Objects\Update $update, ?array $entity = null)
     * @method static \Telegram\Bot\Objects\Message editMessageText(array $params)
     * @method static \Telegram\Bot\Objects\Message editMessageCaption(array $params)
     * @method static \Telegram\Bot\Objects\Message editMessageMedia(array $params)
     * @method static \Telegram\Bot\Objects\Message editMessageReplyMarkup(array $params)
     * @method static \Telegram\Bot\Objects\Poll stopPoll(array $params)
     * @method static bool deleteMessage(array $params)
     * @method static bool deleteMessages(array $params)
     * @method static \Telegram\Bot\Objects\Message sendGame(array $params)
     * @method static \Telegram\Bot\Objects\Message setGameScore(array $params)
     * @method static array getGameHighScores(array $params)
     * @method static \Telegram\Bot\Objects\User getMe()
     * @method static \Telegram\Bot\Objects\UserProfilePhotos getUserProfilePhotos(array $params)
     * @method static \Telegram\Bot\Objects\File getFile(array $params)
     * @method static null|\Psr\Container\ContainerInterface getContainer()
     * @method static bool hasContainer()
     * @method static void useEventDispatcher(\Telegram\Bot\Events\EventDispatcherListenerContract $emitter)
     * @method static \Telegram\Bot\Events\EventDispatcherListenerContract eventDispatcher()
     * @method static bool hasEventDispatcher()
     * @method static void on(string $event, callable $listener, int $priority = 0)
     * @method static \Telegram\Bot\Api setAsyncRequest(bool $isAsyncRequest)
     * @method static \Telegram\Bot\Api setHttpClientHandler(\Telegram\Bot\HttpClients\HttpClientInterface $httpClientHandler)
     * @method static \Telegram\Bot\Api setBaseBotUrl(string $baseBotUrl)
     * @method static null|\Telegram\Bot\TelegramResponse getLastResponse()
     * @method static string downloadFile(\Telegram\Bot\Objects\File|\Telegram\Bot\Objects\BaseObject|string $file, string $filename)
     * @method static string getAccessToken()
     * @method static \Telegram\Bot\Api setAccessToken(string $accessToken)
     * @method static bool isAsyncRequest()
     * @method static int getTimeOut()
     * @method static \Telegram\Bot\Api setTimeOut(int $timeOut)
     * @method static int getConnectTimeOut()
     * @method static \Telegram\Bot\Api setConnectTimeOut(int $connectTimeOut)
     * @method static \Telegram\Bot\TelegramClient getClient()
     * @method static \Telegram\Bot\Objects\Message sendLocation(array $params)
     * @method static \Telegram\Bot\Objects\Message editMessageLiveLocation(array $params)
     * @method static \Telegram\Bot\Objects\Message stopMessageLiveLocation(array $params)
     * @method static void macro($name, $macro)
     * @method static void mixin($mixin, $replace = true)
     * @method static void hasMacro($name)
     * @method static void flushMacros()
     * @method static void macroCall($method, $parameters)
     * @method static \Telegram\Bot\Objects\Message sendMessage(array $params)
     * @method static \Telegram\Bot\Objects\Message forwardMessage(array $params)
     * @method static \Telegram\Bot\Objects\Message copyMessage(array $params)
     * @method static \Telegram\Bot\Objects\Message sendPhoto(array $params)
     * @method static \Telegram\Bot\Objects\Message sendAudio(array $params)
     * @method static \Telegram\Bot\Objects\Message sendDocument(array $params)
     * @method static \Telegram\Bot\Objects\Message sendVideo(array $params)
     * @method static \Telegram\Bot\Objects\Message sendAnimation(array $params)
     * @method static \Telegram\Bot\Objects\Message sendVoice(array $params)
     * @method static \Telegram\Bot\Objects\Message sendVideoNote(array $params)
     * @method static \Telegram\Bot\Objects\Message sendMediaGroup(array $params)
     * @method static \Telegram\Bot\Objects\Message sendVenue(array $params)
     * @method static \Telegram\Bot\Objects\Message sendContact(array $params)
     * @method static \Telegram\Bot\Objects\Message sendPoll(array $params)
     * @method static \Telegram\Bot\Objects\Message sendDice(array $params)
     * @method static bool sendChatAction(array $params)
     * @method static bool setMessageReaction(array $params)
     * @method static void setPassportDataErrors(array $params)
     * @method static \Telegram\Bot\Objects\Message sendInvoice(array $params)
     * @method static bool answerShippingQuery(array $params)
     * @method static bool answerPreCheckoutQuery(array $params)
     * @method static bool answerCallbackQuery(array $params)
     * @method static bool answerInlineQuery(array $params)
     * @method static \Telegram\Bot\Objects\Message sendSticker(array $params)
     * @method static \Telegram\Bot\Objects\StickerSet getStickerSet(array $params)
     * @method static \Telegram\Bot\Objects\File uploadStickerFile(array $params)
     * @method static bool createNewStickerSet(array $params)
     * @method static bool addStickerToSet(array $params)
     * @method static bool setStickerPositionInSet(array $params)
     * @method static bool deleteStickerFromSet(array $params)
     * @method static bool setStickerSetThumb(array $params)
     * @method static array getUpdates(array $params = [], bool $shouldDispatchEvents = true)
     * @method static bool setWebhook(array $params)
     * @method static bool deleteWebhook()
     * @method static \Telegram\Bot\Objects\WebhookInfo getWebhookInfo()
     * @method static \Telegram\Bot\Objects\Update getWebhookUpdate(bool $shouldDispatchEvents = true, ?\Psr\Http\Message\RequestInterface $request = null)
     * @method static bool removeWebhook()
     * @method static \Telegram\Bot\Objects\ForumTopic createForumTopic(array $params)
     * @method static bool editForumTopic(array $params)
     * @method static bool closeForumTopic(array $params)
     * @method static bool reopenForumTopic(array $params)
     * @method static bool deleteForumTopic(array $params)
     * @see \Telegram\Bot\Commands\CommandBus
     * @method static array getCommands()
     * @method static \Telegram\Bot\Commands\CommandBus addCommands(iterable $commands)
     * @method static \Telegram\Bot\Commands\CommandBus addCommand(\Telegram\Bot\Commands\CommandInterface|string $command)
     * @method static \Telegram\Bot\Commands\CommandBus removeCommand(string $name)
     * @method static \Telegram\Bot\Commands\CommandBus removeCommands(array $names)
     */
    class Telegram {
        /**
         * Set the IoC Container.
         *
         * @param \Psr\Container\ContainerInterface $container Container instance
         * @static
         */
        public static function setContainer($container)
        {
            /** @var \Telegram\Bot\BotsManager $instance */
            return $instance->setContainer($container);
        }

        /**
         * Get the configuration for a bot.
         *
         * @throws TelegramBotNotFoundException
         * @static
         */
        public static function getBotConfig($name = null)
        {
            /** @var \Telegram\Bot\BotsManager $instance */
            return $instance->getBotConfig($name);
        }

        /**
         * Get a bot instance.
         *
         * @throws TelegramSDKException
         * @static
         */
        public static function bot($name = null)
        {
            /** @var \Telegram\Bot\BotsManager $instance */
            return $instance->bot($name);
        }

        /**
         * Reconnect to the given bot.
         *
         * @throws TelegramSDKException
         * @static
         */
        public static function reconnect($name = null)
        {
            /** @var \Telegram\Bot\BotsManager $instance */
            return $instance->reconnect($name);
        }

        /**
         * Disconnect from the given bot.
         *
         * @static
         */
        public static function disconnect($name = null)
        {
            /** @var \Telegram\Bot\BotsManager $instance */
            return $instance->disconnect($name);
        }

        /**
         * Determine if given bot name exists.
         *
         * @static
         */
        public static function hasBot($name)
        {
            /** @var \Telegram\Bot\BotsManager $instance */
            return $instance->hasBot($name);
        }

        /**
         * Get the specified configuration value for Telegram.
         *
         * @static
         */
        public static function getConfig($key, $default = null)
        {
            /** @var \Telegram\Bot\BotsManager $instance */
            return $instance->getConfig($key, $default);
        }

        /**
         * Get the default bot name.
         *
         * @static
         */
        public static function getDefaultBotName()
        {
            /** @var \Telegram\Bot\BotsManager $instance */
            return $instance->getDefaultBotName();
        }

        /**
         * Set the default bot name.
         *
         * @static
         */
        public static function setDefaultBot($name)
        {
            /** @var \Telegram\Bot\BotsManager $instance */
            return $instance->setDefaultBot($name);
        }

        /**
         * Return all the created bots.
         *
         * @return array<string, Api>
         * @static
         */
        public static function getBots()
        {
            /** @var \Telegram\Bot\BotsManager $instance */
            return $instance->getBots();
        }

        /**
         * @param list<(string | class-string<CommandInterface>)> $commands A list of command names or FQCNs of CommandInterface instances.
         * @return array An array of commands which includes global and bot specific commands.
         * @deprecated Will be removed in SDK v4
         * @internal Builds the list of commands for the given commands array.
         * @static
         */
        public static function parseBotCommands($commands)
        {
            /** @var \Telegram\Bot\BotsManager $instance */
            return $instance->parseBotCommands($commands);
        }

            }
    }

namespace Livewire {
    /**
     * @see \Livewire\LivewireManager
     */
    class Livewire {
        /**
         * @static
         */
        public static function setProvider($provider)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->setProvider($provider);
        }

        /**
         * @static
         */
        public static function provide($callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->provide($callback);
        }

        /**
         * @static
         */
        public static function component($name, $class = null)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->component($name, $class);
        }

        /**
         * @static
         */
        public static function componentHook($hook)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->componentHook($hook);
        }

        /**
         * @static
         */
        public static function propertySynthesizer($synth)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->propertySynthesizer($synth);
        }

        /**
         * @static
         */
        public static function directive($name, $callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->directive($name, $callback);
        }

        /**
         * @static
         */
        public static function precompiler($callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->precompiler($callback);
        }

        /**
         * @static
         */
        public static function new($name, $id = null)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->new($name, $id);
        }

        /**
         * @static
         */
        public static function isDiscoverable($componentNameOrClass)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->isDiscoverable($componentNameOrClass);
        }

        /**
         * @static
         */
        public static function resolveMissingComponent($resolver)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->resolveMissingComponent($resolver);
        }

        /**
         * @static
         */
        public static function mount($name, $params = [], $key = null)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->mount($name, $params, $key);
        }

        /**
         * @static
         */
        public static function snapshot($component)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->snapshot($component);
        }

        /**
         * @static
         */
        public static function fromSnapshot($snapshot)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->fromSnapshot($snapshot);
        }

        /**
         * @static
         */
        public static function listen($eventName, $callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->listen($eventName, $callback);
        }

        /**
         * @static
         */
        public static function current()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->current();
        }

        /**
         * @static
         */
        public static function findSynth($keyOrTarget, $component)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->findSynth($keyOrTarget, $component);
        }

        /**
         * @static
         */
        public static function update($snapshot, $diff, $calls)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->update($snapshot, $diff, $calls);
        }

        /**
         * @static
         */
        public static function updateProperty($component, $path, $value)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->updateProperty($component, $path, $value);
        }

        /**
         * @static
         */
        public static function isLivewireRequest()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->isLivewireRequest();
        }

        /**
         * @static
         */
        public static function componentHasBeenRendered()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->componentHasBeenRendered();
        }

        /**
         * @static
         */
        public static function forceAssetInjection()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->forceAssetInjection();
        }

        /**
         * @static
         */
        public static function setUpdateRoute($callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->setUpdateRoute($callback);
        }

        /**
         * @static
         */
        public static function getUpdateUri()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->getUpdateUri();
        }

        /**
         * @static
         */
        public static function setScriptRoute($callback)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->setScriptRoute($callback);
        }

        /**
         * @static
         */
        public static function useScriptTagAttributes($attributes)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->useScriptTagAttributes($attributes);
        }

        /**
         * @static
         */
        public static function withUrlParams($params)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withUrlParams($params);
        }

        /**
         * @static
         */
        public static function withQueryParams($params)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withQueryParams($params);
        }

        /**
         * @static
         */
        public static function withCookie($name, $value)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withCookie($name, $value);
        }

        /**
         * @static
         */
        public static function withCookies($cookies)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withCookies($cookies);
        }

        /**
         * @static
         */
        public static function withHeaders($headers)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withHeaders($headers);
        }

        /**
         * @static
         */
        public static function withoutLazyLoading()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->withoutLazyLoading();
        }

        /**
         * @static
         */
        public static function test($name, $params = [])
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->test($name, $params);
        }

        /**
         * @static
         */
        public static function visit($name)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->visit($name);
        }

        /**
         * @static
         */
        public static function actingAs($user, $driver = null)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->actingAs($user, $driver);
        }

        /**
         * @static
         */
        public static function isRunningServerless()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->isRunningServerless();
        }

        /**
         * @static
         */
        public static function addPersistentMiddleware($middleware)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->addPersistentMiddleware($middleware);
        }

        /**
         * @static
         */
        public static function setPersistentMiddleware($middleware)
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->setPersistentMiddleware($middleware);
        }

        /**
         * @static
         */
        public static function getPersistentMiddleware()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->getPersistentMiddleware();
        }

        /**
         * @static
         */
        public static function flushState()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->flushState();
        }

        /**
         * @static
         */
        public static function originalUrl()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->originalUrl();
        }

        /**
         * @static
         */
        public static function originalPath()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->originalPath();
        }

        /**
         * @static
         */
        public static function originalMethod()
        {
            /** @var \Livewire\LivewireManager $instance */
            return $instance->originalMethod();
        }

            }
    }

namespace Yajra\DataTables\Facades {
    /**
     * @mixin \Yajra\DataTables\DataTables
     * @see \Yajra\DataTables\DataTables
     */
    class DataTables {
        /**
         * Make a DataTable instance from source.
         * 
         * Alias of make for backward compatibility.
         *
         * @param object $source
         * @return \Yajra\DataTables\DataTableAbstract
         * @throws \Exception
         * @static
         */
        public static function of($source)
        {
            return \Yajra\DataTables\DataTables::of($source);
        }

        /**
         * Make a DataTable instance from source.
         *
         * @param object $source
         * @return \Yajra\DataTables\DataTableAbstract
         * @throws \Yajra\DataTables\Exceptions\Exception
         * @static
         */
        public static function make($source)
        {
            return \Yajra\DataTables\DataTables::make($source);
        }

        /**
         * Get request object.
         *
         * @static
         */
        public static function getRequest()
        {
            /** @var \Yajra\DataTables\DataTables $instance */
            return $instance->getRequest();
        }

        /**
         * Get config instance.
         *
         * @static
         */
        public static function getConfig()
        {
            /** @var \Yajra\DataTables\DataTables $instance */
            return $instance->getConfig();
        }

        /**
         * DataTables using query builder.
         *
         * @throws \Yajra\DataTables\Exceptions\Exception
         * @static
         */
        public static function query($builder)
        {
            /** @var \Yajra\DataTables\DataTables $instance */
            return $instance->query($builder);
        }

        /**
         * DataTables using Eloquent Builder.
         *
         * @throws \Yajra\DataTables\Exceptions\Exception
         * @static
         */
        public static function eloquent($builder)
        {
            /** @var \Yajra\DataTables\DataTables $instance */
            return $instance->eloquent($builder);
        }

        /**
         * DataTables using Collection.
         *
         * @param \Illuminate\Support\Collection<array-key, array>|array $collection
         * @throws \Yajra\DataTables\Exceptions\Exception
         * @static
         */
        public static function collection($collection)
        {
            /** @var \Yajra\DataTables\DataTables $instance */
            return $instance->collection($collection);
        }

        /**
         * DataTables using Collection.
         *
         * @param \Illuminate\Http\Resources\Json\AnonymousResourceCollection<array-key, array>|array $resource
         * @return \Yajra\DataTables\ApiResourceDataTable|\Yajra\DataTables\DataTableAbstract
         * @static
         */
        public static function resource($resource)
        {
            /** @var \Yajra\DataTables\DataTables $instance */
            return $instance->resource($resource);
        }

        /**
         * @throws \Yajra\DataTables\Exceptions\Exception
         * @static
         */
        public static function validateDataTable($engine, $parent)
        {
            /** @var \Yajra\DataTables\DataTables $instance */
            return $instance->validateDataTable($engine, $parent);
        }

        /**
         * Register a custom macro.
         *
         * @param string $name
         * @param object|callable $macro
         * @param-closure-this static  $macro
         * @return void
         * @static
         */
        public static function macro($name, $macro)
        {
            \Yajra\DataTables\DataTables::macro($name, $macro);
        }

        /**
         * Mix another object into the class.
         *
         * @param object $mixin
         * @param bool $replace
         * @return void
         * @throws \ReflectionException
         * @static
         */
        public static function mixin($mixin, $replace = true)
        {
            \Yajra\DataTables\DataTables::mixin($mixin, $replace);
        }

        /**
         * Checks if macro is registered.
         *
         * @param string $name
         * @return bool
         * @static
         */
        public static function hasMacro($name)
        {
            return \Yajra\DataTables\DataTables::hasMacro($name);
        }

        /**
         * Flush the existing macros.
         *
         * @return void
         * @static
         */
        public static function flushMacros()
        {
            \Yajra\DataTables\DataTables::flushMacros();
        }

            }
    }

namespace Illuminate\Http {
    /**
     */
    class Request extends \Symfony\Component\HttpFoundation\Request {
        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestValidation()
         * @param array $rules
         * @param mixed $params
         * @static
         */
        public static function validate($rules, ...$params)
        {
            return \Illuminate\Http\Request::validate($rules, ...$params);
        }

        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestValidation()
         * @param string $errorBag
         * @param array $rules
         * @param mixed $params
         * @static
         */
        public static function validateWithBag($errorBag, $rules, ...$params)
        {
            return \Illuminate\Http\Request::validateWithBag($errorBag, $rules, ...$params);
        }

        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @param mixed $absolute
         * @static
         */
        public static function hasValidSignature($absolute = true)
        {
            return \Illuminate\Http\Request::hasValidSignature($absolute);
        }

        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @static
         */
        public static function hasValidRelativeSignature()
        {
            return \Illuminate\Http\Request::hasValidRelativeSignature();
        }

        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @param mixed $ignoreQuery
         * @param mixed $absolute
         * @static
         */
        public static function hasValidSignatureWhileIgnoring($ignoreQuery = [], $absolute = true)
        {
            return \Illuminate\Http\Request::hasValidSignatureWhileIgnoring($ignoreQuery, $absolute);
        }

        /**
         * @see \Illuminate\Foundation\Providers\FoundationServiceProvider::registerRequestSignatureValidation()
         * @param mixed $ignoreQuery
         * @static
         */
        public static function hasValidRelativeSignatureWhileIgnoring($ignoreQuery = [])
        {
            return \Illuminate\Http\Request::hasValidRelativeSignatureWhileIgnoring($ignoreQuery);
        }

            }
    /**
     */
    class RedirectResponse extends \Symfony\Component\HttpFoundation\RedirectResponse {
        /**
         * @see \Laravel\Jetstream\JetstreamServiceProvider::boot()
         * @param mixed $message
         * @return \Illuminate\Http\RedirectResponse
         * @static
         */
        public static function banner($message)
        {
            return \Illuminate\Http\RedirectResponse::banner($message);
        }

        /**
         * @see \Laravel\Jetstream\JetstreamServiceProvider::boot()
         * @param mixed $message
         * @return \Illuminate\Http\RedirectResponse
         * @static
         */
        public static function warningBanner($message)
        {
            return \Illuminate\Http\RedirectResponse::warningBanner($message);
        }

        /**
         * @see \Laravel\Jetstream\JetstreamServiceProvider::boot()
         * @param mixed $message
         * @return \Illuminate\Http\RedirectResponse
         * @static
         */
        public static function dangerBanner($message)
        {
            return \Illuminate\Http\RedirectResponse::dangerBanner($message);
        }

            }
    }

namespace Illuminate\Routing {
    /**
     */
    class Route {
        /**
         * @see \Livewire\Features\SupportLazyLoading\SupportLazyLoading::registerRouteMacro()
         * @param mixed $enabled
         * @static
         */
        public static function lazy($enabled = true)
        {
            return \Illuminate\Routing\Route::lazy($enabled);
        }

            }
    }

namespace Illuminate\View {
    /**
     */
    class ComponentAttributeBag {
        /**
         * @see \Livewire\Features\SupportBladeAttributes\SupportBladeAttributes::provide()
         * @param mixed $name
         * @static
         */
        public static function wire($name)
        {
            return \Illuminate\View\ComponentAttributeBag::wire($name);
        }

            }
    /**
     */
    class View {
        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $data
         * @static
         */
        public static function layoutData($data = [])
        {
            return \Illuminate\View\View::layoutData($data);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $section
         * @static
         */
        public static function section($section)
        {
            return \Illuminate\View\View::section($section);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $title
         * @static
         */
        public static function title($title)
        {
            return \Illuminate\View\View::title($title);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $slot
         * @static
         */
        public static function slot($slot)
        {
            return \Illuminate\View\View::slot($slot);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $view
         * @param mixed $params
         * @static
         */
        public static function extends($view, $params = [])
        {
            return \Illuminate\View\View::extends($view, $params);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param mixed $view
         * @param mixed $params
         * @static
         */
        public static function layout($view, $params = [])
        {
            return \Illuminate\View\View::layout($view, $params);
        }

        /**
         * @see \Livewire\Features\SupportPageComponents\SupportPageComponents::registerLayoutViewMacros()
         * @param callable $callback
         * @static
         */
        public static function response($callback)
        {
            return \Illuminate\View\View::response($callback);
        }

            }
    }


namespace  {
    class Helper extends \App\Helpers\Helpers {}
    class NoCaptcha extends \Anhskohbo\NoCaptcha\Facades\NoCaptcha {}
    class Telegram extends \Telegram\Bot\Laravel\Facades\Telegram {}
    class Livewire extends \Livewire\Livewire {}
    class DataTables extends \Yajra\DataTables\Facades\DataTables {}
}





