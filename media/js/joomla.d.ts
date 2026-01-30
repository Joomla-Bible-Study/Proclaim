/**
 * Type definitions for Joomla global object
 * This file helps the IDE resolve the 'Joomla' variable and provides code completion.
 */

declare var Joomla: {
    /**
     * Get Joomla options
     * @param key The option key
     */
    getOptions: (key: string) => any;

    /**
     * Joomla Modal handling
     */
    Modal: {
        getCurrent: () => {
            close: () => void;
        };
    };

    /**
     * Joomla Editors
     */
    editors: {
        instances: {
            [key: string]: {
                replaceSelection: (text: string) => void;
                getValue: () => string;
                setValue: (text: string) => void;
            };
        };
    };

    /**
     * Allow other properties
     */
    [key: string]: any;
};

// Also declare it on the window object
interface Window {
    Joomla: typeof Joomla;
    jSelectLocations: (id: string, title: string, catid: string, object: any, link: string, lang: string) => boolean;
    jSelectTeachers: (id: string, title: string, catid: string, object: any, link: string, lang: string) => boolean;
    jSelectSeries: (id: string, title: string, catid: string, object: any, link: string, lang: string) => boolean;
    jSelectMessages: (id: string, title: string, catid: string, object: any, link: string, lang: string) => boolean;
    jSelectServer: (id: string, title: string, object: any, link: string, lang: string) => boolean;
    jSelectType: (id: string, title: string, catid: string, object: any, link: string, lang: string) => boolean;
}
