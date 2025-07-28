import { EditorState } from "@codemirror/state";
import { EditorView, keymap } from "@codemirror/view";
import { defaultKeymap } from "@codemirror/commands";
import { basicSetup } from "codemirror";
import {python} from "@codemirror/lang-python"
import { java } from "@codemirror/lang-java";
import {cpp} from "@codemirror/lang-cpp"
import {markdown} from "@codemirror/lang-markdown"
import { lineNumbers } from "@codemirror/view"
import { history, historyKeymap } from '@codemirror/commands';
import { placeholder } from "@codemirror/view";

    // Initialize Constant Variables
    const initial_solution_div = document.getElementById("initial-solution-div");
    const test_case_div = document.getElementById("test-case-div");
    const select_form = document.getElementById('programming_language');
    const supported_languages = {};
    const available_languages = ['c++', 'java', 'python'];

    let previous_language = null;
    let currentLanguage = null;

    // Add ability to switch programming languages and have their own codes
    function switchLanguageFromEvent(event) {
        const selected_language = event.target.value;
        switchLanguage(selected_language);
    }

    function switchLanguage(language) {
        currentLanguage = language;
        if (!initial_solution_editor || !test_case_editor) return;

        let extension;
        switch (language) {
            case 'java':
                extension = java();
                break;
            case 'python':
                extension = python();
                break;
            case 'c++':
                extension = cpp();
                break;
            default:
                console.warn("Unsupported language:", language);
                return;
        }
    }

    function loadSupportedLanguages(question_supported_languages) {
        const currentLanguage =  Object.keys(question_supported_languages)[0];
        for (const language in question_supported_languages) {
            const values = question_supported_languages[language];
            let extension;

            switch (language) {
                case "python":
                    extension = python();
                    break;
                case "java":
                    extension = java();
                    break;
                case "c++":
                    extension = cpp();
                    break;
                default:
                    console.warn(`Unsupported language: ${language}`);
                    continue;
            }

            const initial_solution_state = EditorState.create({
                doc: values.initial_solution || "",
                extensions: [basicSetup, extension]
            });
            const test_case_state = EditorState.create({
                doc: values.test_case || "",
                extensions: [basicSetup, extension]
            });

            supported_languages[language] = {
                initial_solution: initial_solution_state,
                test_case: test_case_state,
                is_valid: true
            };

            if (currentLanguage === language) {
                initial_solution_editor.setState(initial_solution_state);
                test_case_editor.setState(test_case_state);
            }
        }
    }

    let initial_solution_editor = null;
    let test_case_editor = null;

    function createEditor(parent, doc = '', languageExtension = null) {
        const extensions = [basicSetup];
        if (languageExtension) extensions.push(languageExtension);

        const state = EditorState.create({ doc, extensions });
        return new EditorView({ state, parent });
    }

    function initializeEditors() {
        const initial_solution_div = document.getElementById("initial-solution-div");
        const test_case_div = document.getElementById("test-case-div");
        const select_form = document.getElementById("programming_language");

        if (!initial_solution_div || !test_case_div || !select_form) {
            console.warn("Editor elements not found. Skipping initialization.");
            return;
        }

        // Create fresh editors every time after DOM swap
        initial_solution_editor = createEditor(initial_solution_div);
        test_case_editor = createEditor(test_case_div);

        initializeCodingQuestionPage();
    }


    function initializeCodingQuestionPage() {
        const select_form = document.getElementById("programming_language");
        if (!select_form) return;

        select_form.addEventListener('focus', () => {
        previous_language = select_form.value;
        });

        const select = document.getElementById('programming_language');
        if (!select || select.dataset.initialized === "true") return;
        console.log(window.question_supported_languages)
        loadSupportedLanguages(window.question_supported_languages);

        switchLanguageFromEvent({ target: { value: select.value } });
        select.dataset.initialized = "true"; 
    }

    document.body.addEventListener("htmx:afterSettle", function () {
        const container = document.getElementById("coding-question");
        if (!container) return;

        try {
            const instruction = JSON.parse(container.dataset.instruction || '{}');
            const languageCodes = JSON.parse(container.dataset.languageCodes || '{}');
            console.log(instruction, languageCodes);
            window.instruction = instruction;
            window.question_supported_languages = languageCodes;

            // Actually reinitialize editors and load languages
            initializeEditors();
        } catch (e) {
            console.error("[HTMX] Failed to parse editor data:", e);
        }
    });


    document.addEventListener("DOMContentLoaded", () => {
        const container = document.getElementById("coding-question");
        if (!container) return;

        try {
            const instruction = JSON.parse(container.dataset.instruction || '{}');
            const languageCodes = JSON.parse(container.dataset.languageCodes || '{}');

            window.instruction = instruction;
            window.question_supported_languages = languageCodes;

            initializeEditors(); // <-- Run here for page load
        } catch (e) {
            console.error("[DOM] Failed to parse editor data:", e);
        }
    });

window.initializeCodingQuestionPage = initializeCodingQuestionPage;
window.initializeEditors = initializeEditors; 

