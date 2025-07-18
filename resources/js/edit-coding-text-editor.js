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
    const solution_div = document.getElementById("solution-div");
    const initial_solution_div = document.getElementById("initial-solution-div");
    const test_case_div = document.getElementById("test-case-div");
    const select_form = document.getElementById('programming_language');
    const available_languages = ['c++', 'java', 'python'];
    const supported_languages = {};
    let instruction_div = document.getElementById('instruction-div');
    let instruction_previous_state = null;
    let previous_language = null;
    let currentLanguage = null;
    

    const solution_editor = createEditor(solution_div);
    const initial_solution_editor = createEditor(initial_solution_div);
    const test_case_editor = createEditor(test_case_div);


    // Initialize States for Code Editors
    const instruction_state = EditorState.create({
        doc:window.instruction,
        extensions: [
            placeholder("Type your Instructions here"),
            markdown(),            
            history(),                      
            keymap.of([
              ...defaultKeymap,
              ...historyKeymap               
            ]),
            EditorView.lineWrapping,

            EditorView.theme({
              "&": {
                backgroundColor: "#ffffff",
                border: "1px solid #e0e0e0",
                fontFamily: "sans-serif",
                fontSize: "14px",
              },
              ".cm-content": {
                minHeight: "16rem",
                padding: "1rem"
              },
              ".cm-scroller": {
                minHeight: "16rem",
                maxHeight:"24rem",
                width: "100%",
                height: "100%",   
                overflow: "auto"
              }
            }, { dark: false })
          ],
    });
    let instruction_editor = new EditorView({
        state: instruction_state,
        parent: instruction_div
    });

    function createEditor(parent) {
        const state = EditorState.create({ extensions: [basicSetup] });
        return new EditorView({ state, parent });
      }

    const onChangeListener = EditorView.updateListener.of((update) => {
        if (update.docChanged && currentLanguage) {

            if (supported_languages[currentLanguage]) {
                supported_languages[currentLanguage].is_valid = false;
                
                supported_languages[currentLanguage].complete_solution = solution_editor.state;
                supported_languages[currentLanguage].initial_solution = initial_solution_editor.state;
                supported_languages[currentLanguage].test_case = test_case_editor.state;
                updateLanguageBadges();
                console.log(`[${currentLanguage}] marked as invalid and states updated`);
            } else {
                console.warn(`Language not found: ${currentLanguage}`);
            }
        }
    });


    // Add ability to switch programming languages and have their own codes
    function switchLanguageFromEvent(event) {
        const selected_language = event.target.value;
      
        select_form.addEventListener('pointerdown', () => {
          previous_language = select_form.value;
        });
        console.log(`${previous_language} is included in available languages? ${available_languages.includes(previous_language)}`);

        if (previous_language && previous_language != selected_language && available_languages.includes(previous_language)) {
            console.log('got run')
            updateSupportedLanguages({
                language: previous_language,
                complete_solution: solution_editor.state,
                initial_solution: initial_solution_editor.state,
                test_case: test_case_editor.state,
            });
        }
        switchLanguage(selected_language);
    }

    // Reinitialize code editors to switch languages
    function switchLanguage(language) {
        currentLanguage = language;
        if (supported_languages.hasOwnProperty(language) && supported_languages[language] != null) {
            solution_editor.setState(supported_languages[language]['complete_solution']);
            initial_solution_editor.setState(supported_languages[language]['initial_solution']);
            test_case_editor.setState(supported_languages[language]['test_case']);

        } else {
                let languageExtension;
                let placeholder_solution = placeholder(`Insert complete solution here`);
                let placeholder_initial = placeholder(`This Code will be given to students.`);
                let placeholder_test = placeholder(`Insert test case here`);
                let doc_test = "";
            
                switch (language) {
                    case 'java':
                        languageExtension = java();
                        doc_test = 
`import org.junit.jupiter.api.Test;
import static org.junit.jupiter.api.Assertions.*;

// Java uses JUnit5 for testing

class <YourClass+Test> {     
    @Test
    void <YourTestCaseName> {
    }
}`;
                        break;
                    case 'python':
                        languageExtension = python();
                        break;
                    case 'c++':
                        languageExtension = cpp();
                        break;
                    default:
                        console.warn("Unsupported language:", language);
                        return;
                }

            const new_solution_state = EditorState.create({
                extensions: [basicSetup, languageExtension, placeholder_solution, onChangeListener],
            });
            const new_initial_solution_state = EditorState.create({
                extensions: [basicSetup, languageExtension, placeholder_initial, onChangeListener],
            });
            const new_test_case_state = EditorState.create({
                doc: doc_test,
                extensions: [basicSetup, languageExtension, placeholder_test, onChangeListener],
            });
            
            solution_editor.setState(new_solution_state);
            initial_solution_editor.setState(new_initial_solution_state);
            test_case_editor.setState(new_test_case_state);

        }

        updateSupportedLanguages({
            language,
            complete_solution: solution_editor.state,
            initial_solution: initial_solution_editor.state,
            test_case: test_case_editor.state,
        });
        console.log(supported_languages);

    }
    
    // Create language object and add to an array to keep track
    function updateSupportedLanguages({ language, ...updates }) {
        if (!language) {
            throw new Error("The 'language' field is required.");
        }
    
        if (!supported_languages[language]) {
            supported_languages[language] = {
                complete_solution: '',
                initial_solution: '',
                test_case: '',
                is_valid: false
            };
        }
    
        Object.assign(supported_languages[language], updates);
    }
    
    function getSolutionCode() {
        const processed = {};

        for (const lang in supported_languages) {
            const entry = supported_languages[lang];
            if (entry.is_valid){
                processed[lang] = {
                    complete_solution: entry.complete_solution.doc.toString(),
                    initial_solution: entry.initial_solution.doc.toString(),
                    test_case: entry.test_case.doc.toString(),
                    is_valid: entry.is_valid,
                }
            };
        }

        document.getElementById('code-input').value = JSON.stringify(processed);
    }
    

    function getTestCaseCode() {
        document.getElementById('test-input').value = test_case_editor.state.doc.toString();
    }

    function validateLanguageCompleteSolution(){
        document.getElementById('validate-complete-solution-input').value = solution_editor.state.doc.toString();
        document.getElementById('validate-test-case-input').value = test_case_editor.state.doc.toString();
        document.getElementById('language_to_validate-input').value = select_form.value;
    }

    function getInstructionCode() {
        document.getElementById('instruction-input').value = instruction_editor.state.doc.toString();
    }
    
    function previewInstructionCode() {    
        if (instruction_editor.state) {
            instruction_previous_state = instruction_editor.state;
        } else {
            instruction_previous_state = EditorState.create({
                doc: instruction_editor.state.doc.toString(),
                extensions: [
                    markdown(),            
                    history(),                       
                    keymap.of([ 
                        ...defaultKeymap,
                        ...historyKeymap               
                    ]),
                    EditorView.lineWrapping,

                    EditorView.theme({
                    "&": {
                        backgroundColor: "#ffffff",
                        border: "1px solid #e0e0e0",
                        fontFamily: "sans-serif",
                        fontSize: "14px",
                    },
                    ".cm-content": {
                        minHeight: "16rem",
                        padding: "1rem"
                    },
                    ".cm-scroller": {
                        minHeight: "16rem",
                        maxHeight:"24rem",
                        width: "100%",
                        height: "100%",   
                        overflow: "auto"
                    }
                    }, { dark: false })
                ],  
            });

        }
        let input = document.getElementById('instruction-preview-input');

        if (input) {
            input.value = instruction_editor.state.doc.toString();
        } else {
            console.warn("Element #instruction-preview-input not found.");
        }
    }
    
    function getPreviousInstructionCode() {
        setTimeout(() => {
            if (instruction_editor) {
                instruction_editor.destroy(); 
            }

            instruction_div.innerHTML = "";

            instruction_editor = new EditorView({
                state: instruction_previous_state,
                parent: instruction_div
            });
        }, 500);
    }

    function attachButtonsListener() {
        const preview_button = document.getElementById("preview-button");
        const edit_button = document.getElementById("edit-button");        
        if (preview_button) {
            preview_button.addEventListener("click", previewInstructionCode);
        }
        if (edit_button) {
            edit_button.addEventListener("click", getPreviousInstructionCode);
        }
    }

    document.body.addEventListener("htmx:afterSwap", function(evt) {
        instruction_div = document.getElementById("instruction-div");      

        if (['preview-button', 'edit-button'].includes(evt.detail.target.id)) {
            attachButtonsListener();
        }
        
    });

    document.body.addEventListener("htmx:load", function(evt) {
        initializeCodingQuestionPage();
    });

    function initializeCodingQuestionPage() {
        const select = document.getElementById('programming_language');
        if (!select || select.dataset.initialized === "true") return;

        console.log("Initializing CodeMirror");
        attachButtonsListener();
        console.log(window.question_supported_languages);
        loadSupportedLanguages(window.question_supported_languages);

        switchLanguageFromEvent({ target: { value: select.value } });
        select.dataset.initialized = "true"; 
    }


    function temporarilyDisable(button) {
        setTimeout(() => {
            button.disabled = true;
            setTimeout(() => {
              button.disabled = false;
            }, 1000); // 1 second cooldown
          }, 0);
      }
      
    document.body.addEventListener('htmx:afterRequest', function () {
        const input = document.getElementById('language-validation-status');
        if (input) {
            const language = input.dataset.language;
            const isValid = input.dataset.valid === 'true';

            if (supported_languages[language]) {
                supported_languages[language].is_valid = isValid;
                console.log(`✅ ${language} marked as valid:`, isValid);
                updateLanguageBadges();
            }

            input.remove(); 
        }
    });

    function updateLanguageBadges() {
        const select = document.getElementById('programming_language');
        for (const option of select.options) {
            const lang = option.value;
            if (supported_languages[lang]?.is_valid) {
                if (!option.text.includes('✅')) {
                    option.text = `${lang} ✅`;
                }
            } else {
                option.text = lang;
            }
        }
    }
    

    function loadSupportedLanguages(question_supported_languages) {
        console.log( Object.keys(question_supported_languages)[0]);
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

            const complete_solution_state = EditorState.create({
                doc: values.complete_solution || "",
                extensions: [basicSetup, extension, onChangeListener]
            });
            const initial_solution_state = EditorState.create({
                doc: values.initial_solution || "",
                extensions: [basicSetup, extension, onChangeListener]
            });
            const test_case_state = EditorState.create({
                doc: values.test_case || "",
                extensions: [basicSetup, extension, onChangeListener]
            });

            supported_languages[language] = {
                complete_solution: complete_solution_state,
                initial_solution: initial_solution_state,
                test_case: test_case_state,
                is_valid: true
            };

            console.log(supported_languages);

            if (currentLanguage === language) {
                solution_editor.setState(complete_solution_state);
                initial_solution_editor.setState(initial_solution_state);
                test_case_editor.setState(test_case_state);
            }
        }

        updateLanguageBadges();
    }

window.switchLanguageFromEvent = switchLanguageFromEvent;
window.switchLanguage = switchLanguage;
window.getSolutionCode = getSolutionCode;
window.getTestCaseCode = getTestCaseCode;
window.getInstructionCode = getInstructionCode;
window.validateLanguageCompleteSolution = validateLanguageCompleteSolution;
window.temporarilyDisable = temporarilyDisable;




