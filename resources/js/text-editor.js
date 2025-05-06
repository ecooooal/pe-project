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

const solution_div = document.getElementById("solution-div");
const test_case_div = document.getElementById("test-case-div");
const select_form = document.getElementById('programming_language');
const available_languages = ['c++', 'java', 'python'];
const supported_languages = {};
let instruction_div = document.getElementById('instruction-div');
let instruction_previous_state = null;
let previous_language = null;


    export const solution_state = EditorState.create({
        extensions: [basicSetup],
    });
    export const solution_editor = new EditorView({
        state: solution_state,
        parent: solution_div
    });

    export const test_case_state = EditorState.create({
        extensions: [basicSetup],
    });
    export const test_case_editor = new EditorView({
        state: test_case_state,
        parent: test_case_div
    });

    export const instruction_state = EditorState.create({
        doc: `Type your Instructions here`,
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
    let instruction_editor = new EditorView({
        state: instruction_state,
        parent: instruction_div
    });


    function switchLanguageFromEvent(event) {
        const selected_language = event.target.value;
      
        select_form.addEventListener('pointerdown', () => {
          previous_language = select_form.value;
        });
        if (previous_language && previous_language != selected_language && available_languages.includes(previous_language)) {
            console.log('got run')
            updateSupportedLanguages({
                language: previous_language,
                complete_solution: solution_editor.state,
                initial_solution: 'none',
                test_case: test_case_editor.state
            });
        }
        switchLanguage(selected_language);
    }

    function switchLanguage(language) {
        let languageExtension;
        let doc_solution;
        let doc_test;
    
        switch (language) {
            case 'java':
                languageExtension = java();
                doc_solution = `q`;
                doc_test = `w`;
                break;
            case 'python':
                languageExtension = python();
                doc_solution = `e`;
                doc_test = `r`;
                break;
            case 'c++':
                languageExtension = cpp();
                doc_solution = `t`;
                doc_test = `y`;
                break;
            default:
                console.warn("Unsupported language:", language);
                return;
        }

        if (supported_languages.hasOwnProperty(language) && supported_languages[language] != null) {
            solution_editor.setState(supported_languages[language]['complete_solution']);
            test_case_editor.setState(supported_languages[language]['test_case']);

        } else {
            const new_solution_state = EditorState.create({
                doc: doc_solution,
                extensions: [basicSetup, languageExtension],
            });
            const new_test_case_state = EditorState.create({
                doc: doc_test,
                extensions: [basicSetup, languageExtension],
            });
            solution_editor.setState(new_solution_state);
            test_case_editor.setState(new_test_case_state);

        }

        updateSupportedLanguages({
            language,
            complete_solution: solution_editor.state,
            initial_solution: 'none',
            test_case: test_case_editor.state
        });
    }
    

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
        const language = document.getElementById('programming_language').value;
    
        if (language) {
            updateSupportedLanguages({
                language,
                complete_solution: solution_editor.state,
                initial_solution: 'none',
                test_case: test_case_editor.state
            });
    
            console.log('Is language validated?', supported_languages[language]['is_valid']);
        }
    
        document.getElementById('code-input').value = JSON.stringify(supported_languages);
    }
    

    function getTestCaseCode() {
        document.getElementById('test-input').value = test_case_editor.state.doc.toString();
    }

    function validateLanguageCompleteSolution(){
        const language = document.getElementById('programming_language').value;
        updateSupportedLanguages({ language, is_valid: true });

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
    
    function getPreviousInstructionCode(){
        setTimeout(() => {
            instruction_editor = new EditorView({
                state: instruction_previous_state,
                parent: instruction_div
            });
        }, 250); 
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
    document.addEventListener("DOMContentLoaded", () => {
        const programming_language_selected = document.getElementById('programming_language');
        attachButtonsListener();
        if (programming_language_selected) {
            switchLanguageFromEvent({ target: { value: programming_language_selected.value } });
        }   
    });


window.switchLanguageFromEvent = switchLanguageFromEvent;
window.switchLanguage = switchLanguage;
window.getSolutionCode = getSolutionCode;
window.getTestCaseCode = getTestCaseCode;
window.getInstructionCode = getInstructionCode;
window.validateLanguageCompleteSolution = validateLanguageCompleteSolution;
window.previewInstructionCode = previewInstructionCode;
window.getPreviousInstructionCode = getPreviousInstructionCode;




